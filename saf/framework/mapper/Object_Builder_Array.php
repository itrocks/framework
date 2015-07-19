<?php
namespace SAF\Framework\Mapper;

use SAF\Framework\Builder;
use SAF\Framework\Dao;
use SAF\Framework\Reflection\Annotation\Class_;
use SAF\Framework\Reflection\Annotation\Property\Link_Annotation;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Reflection\Type;
use SAF\Framework\Tools\Password;
use SAF\Framework\Tools\Stringable;
use SAF\Framework\View\Html\Builder\Property;

/**
 * Build an object and it's property values from data stored into a recursive array
 */
class Object_Builder_Array
{

	//------------------------------------------------------------------------------------- $builders
	/**
	 * @var Object_Builder_Array[] key is the property name
	 */
	private $builders;

	//-------------------------------------------------------------------------------- $built_objects
	/**
	 * The objects that where built : get it with getBuiltObjects()
	 *
	 * @var array
	 */
	private $built_objects;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Reflection_Class
	 */
	private $class;

	//------------------------------------------------------------------------------------- $defaults
	/**
	 * Default values for each class property
	 *
	 * @var array
	 */
	private $defaults;

	//---------------------------------------------------------------------------------- $use_widgets
	/**
	 * True (default) if apply build specifics for arrays that come from an input form :
	 * - apply arrayFormRevert to split key positions
	 * - apply widgets
	 * Setting this to false disable these specific processes.
	 *
	 * @var boolean
	 */
	private $from_form;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * Properties list, set by start()
	 *
	 * @var Reflection_Property[]
	 */
	private $properties;

	//-------------------------------------------------------------------------------------- $started
	/**
	 * True when start() is called. Back to false by stop(). This avoids resetting data when recurse
	 *
	 * @var boolean
	 */
	private $started = false;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 * @param $from_form  boolean Set this to false to disable interpretation of arrays coming from
	 *                    forms : arrayFormRevert, widgets. You should always set this to false if
	 *                    your array does not come from an input form.
	 */
	public function __construct($class_name = null, $from_form = true)
	{
		$this->from_form = $from_form;
		if (isset($class_name)) {
			$this->setClass($class_name);
		}
	}

	//------------------------------------------------------------------------------------ __destruct
	public function __destruct()
	{
		if ($this->started) {
			$this->stop();
		}
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @param $array                array
	 * @param $object               object
	 * @param $null_if_empty        boolean
	 * @param $ignore_property_name string
	 * @return object
	 */
	public function build(
		$array, $object = null, $null_if_empty = false, $ignore_property_name = null
	) {
		if (!$this->started) {
			$this->start(isset($object) ? get_class($object) : null);
		}
		$search = $this->initObject($array, $object);
		$build = new Object_Builder_Array_Tool(
			$array, $object, $null_if_empty, $ignore_property_name, $search
		);
		$this->buildProperties($build);
		$this->buildSubObjects($build);
		if ($build->is_null) {
			return null;
		}
		else {
			if ($build->read_properties) {
				$object = $this->readObject($object, $build->read_properties);
			}
			$this->built_objects[] = $build->object;
			return $object;
		}
	}

	//------------------------------------------------------------------------------- buildBasicValue
	/**
	 * @param $property Reflection_Property
	 * @param $value    boolean|integer|float|string|array
	 * @return boolean|integer|float|string|array
	 */
	private function buildBasicValue(Reflection_Property $property, $value)
	{
		if (!is_null($value) || !$property->getAnnotation('null')->value) {
			switch ($property->getType()->asString()) {
				case Type::BOOLEAN:
					$value = !(empty($value) || ($value === 'false'));
					break;
				case Type::INTEGER:
					$value = intval($value);
					break;
				case Type::FLOAT:
					$value = floatval($value);
					break;
			}
		}
		return $value;
	}

	//-------------------------------------------------------------------------- buildCollectionValue
	/**
	 * Accepted arrays :
	 * $array[$object_number][$property_name] = $value
	 * $array[$property_name][$object_number] = $value
	 * $array[0][$column_number] = 'property_name' then $array[$object_number][$column_number] = $value
	 *
	 * @param $class_name    string
	 * @param $array         array
	 * @param $null_if_empty boolean
	 * @param $parent        object the parent object, if linked
	 * @return object[]
	 */
	public function buildCollection($class_name, $array, $null_if_empty = false, $parent = null)
	{
		$collection = [];
		if ($array) {
			$builder = new Object_Builder_Array($class_name);
			/** @var $link Class_\Link_Annotation */
			$link = $builder->class->getAnnotation('link');
			if ($link->value && isset($parent->id)) {
				$composite_properties = call_user_func(
					[$builder->class->name, 'getCompositeProperties'], $this->class->name
				);
				$id_property_name = 'id_' . reset($composite_properties);
			}
			else {
				$id_property_name = null;
			}
			// replace $array[$property_name][$object_number] with $array[$object_number][$property_name]
			reset($array);
			if ($this->from_form && !is_numeric(key($array))) {
				$array = arrayFormRevert($array);
			}
			// check if the first row contains column names
			$first_row = reset($array);
			reset($first_row);
			if ($combine = is_numeric(key($first_row))) {
				unset($array[key($array)]);
			}
			foreach ($array as $key => $element) {
				if ($combine) {
					$element = array_combine($first_row, $element);
				}
				if ($id_property_name && !isset($element[$id_property_name])) {
					$element[$id_property_name] = $parent->id;
				}
				$object = $builder->build($element, null, $null_if_empty, $id_property_name);
				if (isset($object)) {
					$collection[$key] = $object;
				}
			}
		}
		return $collection;
	}

	//--------------------------------------------------------------------------- buildDottedProperty
	/**
	 * @param $build         Object_Builder_Array_Tool
	 * @param $property_name string The name of the property
	 * @param $value         mixed The value of the property
	 * @param $pos           integer The position of the DOT into the $property_name
	 */
	private function buildDottedProperty(Object_Builder_Array_Tool $build, $property_name, $value, $pos)
	{
		$property_path = substr($property_name, $pos + 1);
		$property_name = substr($property_name, 0, $pos);
		$this->extractAsterisk($property_name);
		$property = isset($this->properties[$property_name]) ? $this->properties[$property_name] : null;
		if (isset($property)) {
			$build->objects[$property->name][$property_path] = $value;
		}
	}

	//------------------------------------------------------------------------------- buildIdProperty
	/**
	 * If an id_foo property is set and not empty, it can be set and associated object is removed
	 * id_foo must always be set before any forced foo[sub_property] values into the array
	 *
	 * @param $object        object
	 * @param $property_name string must start with 'id_'
	 * @param $value         integer
	 * @param $null_if_empty boolean
	 * @return boolean
	 */
	private function buildIdProperty($object, $property_name, $value, $null_if_empty)
	{
		$is_null = $null_if_empty;
		$real_property_name = substr($property_name, 3);
		$property = $this->properties[$real_property_name];
		if (empty($value)) {
			$value = $property->getAnnotation('null')->value ? null : 0;
		}
		if (isset($object->$real_property_name) && (
			empty($value)
			|| !isset($object->$real_property_name->id)
			|| ($value != $object->$real_property_name->id)
		)) {
			$object->$real_property_name = null;
		}
		$object->$property_name = $value;
		if (!$property->isValueEmptyOrDefault($value)) {
			$is_null = false;
		}
		return $is_null;
	}

	//--------------------------------------------------------------------------------- buildMapValue
	/**
	 * @param $array      array
	 * @param $class_name string the class name to build each element
	 * @param $link       string|null
	 * @return integer[]
	 */
	public function buildMap($array, $class_name, $link = Link_Annotation::MAP)
	{
		$map = [];
		if ($array) {
			foreach ($array as $key => $element) {
				if (!empty($element)) {
					$map[$key] = is_array($element)
						? (new Object_Builder_Array($class_name))->build($element)
						: (
							is_object($element)
							? $element
							: ($link ? Dao::read($element, $class_name) : intval($element))
						);
				}
			}
		}
		return $map;
	}

	//------------------------------------------------------------------------------ buildObjectValue
	/**
	 * @param $class_name    string
	 * @param $array         array
	 * @param $null_if_empty boolean
	 * @return object
	 */
	private function buildObjectValue($class_name, $array, $null_if_empty = false)
	{
		$builder = new Object_Builder_Array($class_name);
		$object = $builder->build($array, null, $null_if_empty);
		$this->built_objects = array_merge($this->built_objects, $builder->built_objects);
		return $object;
	}

	//------------------------------------------------------------------------------- buildProperties
	/**
	 * @param $build Object_Builder_Array_Tool
	 */
	private function buildProperties(Object_Builder_Array_Tool $build)
	{
		foreach ($build->array as $property_name => $value) {
			if ($pos = strpos($property_name, DOT)) {
				$this->buildDottedProperty($build, $property_name, $value, $pos);
			}
			else {
				$this->buildSimpleProperty($build, $property_name, $value);
			}
		}
	}

	//--------------------------------------------------------------------------------- buildProperty
	/**
	 * @param $object        object
	 * @param $property      Reflection_Property
	 * @param $value         string
	 * @param $null_if_empty boolean
	 * @return boolean true if property value is null
	 */
	private function buildProperty($object, Reflection_Property $property, $value, $null_if_empty)
	{
		$is_null = $null_if_empty;
		// use widget
		if (
			$this->from_form
			&& ($builder = $property->getAnnotation('widget')->value)
			&& is_a($builder, Property::class, true)
		) {
			$builder = Builder::create($builder, [$property, $value]);
			/** @var $builder Property */
			$value2 = $builder->buildValue($object, $null_if_empty);
			if ($value2 !== Property::DONT_BUILD_VALUE) {
				$value = $value2;
				$done = true;
			}
		}
		if (!isset($done)) {
			$type = $property->getType();
			if ($type->isBasic()) {
				// password
				if ($encryption = $property->getAnnotation('password')->value) {
					if ($value == Password::UNCHANGED) {
						return true;
					}
					$value = (new Password($value, $encryption))->encrypted();
				}
				// others basic values
				else {
					$value = $this->buildBasicValue($property, $value);
				}
			}
			elseif (is_array($value)) {
				$link = $property->getAnnotation('link')->value;
				// object
				if ($link == Link_Annotation::OBJECT) {
					$class_name = $property->getType()->asString();
					$value = $this->buildObjectValue($class_name, $value, $null_if_empty);
				}
				// collection
				elseif ($link == Link_Annotation::COLLECTION) {
					$class_name = $property->getType()->getElementTypeAsString();
					$value = $this->buildCollection($class_name, $value, $null_if_empty, $object);
				}
				// map or not-linked array
				else {
					$value = $this->buildMap($value, $property->getType()->getElementTypeAsString(), $link);
				}
			}
			// @output string
			elseif (isset($value) && ($property->getAnnotation('output')->value == 'string')) {
				/** @var $object_value Stringable */
				$object_value = Builder::create($property->getType()->asString());
				$object_value->fromString($value);
				$value = $object_value;
			}
		}
		// the property value is set only for official properties, if not default and not empty
		$property_name = $property->name;
		if (($value !== '') || !$property->getType()->isClass()) {
			$object->$property_name = $value;
		}
		if (!$property->isValueEmptyOrDefault($value)) {
			$is_null = false;
		}
		return $is_null;
	}

	//--------------------------------------------------------------------------- buildSimpleProperty
	/**
	 * Builds a simple-name property (no DOT)
	 *
	 * @param $build         Object_Builder_Array_Tool
	 * @param $property_name string
	 * @param $value         mixed
	 */
	private function buildSimpleProperty(Object_Builder_Array_Tool $build, $property_name, $value)
	{
		$asterisk = $this->extractAsterisk($property_name);
		$property = isset($this->properties[$property_name]) ? $this->properties[$property_name] : null;
		if (substr($property_name, 0, 3) === 'id_') {
			if (
				!$this->buildIdProperty($build->object, $property_name, $value, $build->null_if_empty)
				&& (
					($build->ignore_property_name !== $property_name)
					|| !isset($build->search[substr($property_name, 3)])
				)
			) {
				$build->is_null = false;
			}
		}
		elseif (($property_name != 'id') && !isset($property)) {
			trigger_error(
				'Unknown property ' . $this->class->name . '::$' . $property_name, E_USER_ERROR
			);
		}
		elseif (!(
			$property && $this->buildProperty($build->object, $property, $value, $build->null_if_empty)
		)) {
			$build->is_null = false;
		}
		if ($asterisk) {
			$build->read_properties[$property_name] = $value;
		}
	}

	//-------------------------------------------------------------------------------- buildSubObject
	/**
	 * @param $object        object
	 * @param $property      Reflection_Property
	 * @param $value         mixed
	 * @param $null_if_empty boolean
	 * @return boolean
	 */
	private function buildSubObject($object, Reflection_Property $property, $value, $null_if_empty)
	{
		$is_null = $null_if_empty;
		$property_name = $property->name;
		$type = $property->getType();
		if (!isset($this->builders[$property_name])) {
			$this->builders[$property_name] = new Object_Builder_Array($type->getElementTypeAsString());
		}
		$builder = $this->builders[$property_name];
		$value = $builder->build($value, null, $null_if_empty);
		if (isset($value)) {
			if ($type->isMultiple()) {
				$object->$property_name;
				array_push($object->$property_name, $value);
			}
			else {
				$object->$property_name = $value;
			}
			$is_null = false;
		}
		return $is_null;
	}

	//------------------------------------------------------------------------------- buildSubObjects
	/**
	 * @param $build Object_Builder_Array_Tool
	 */
	private function buildSubObjects(Object_Builder_Array_Tool $build)
	{
		foreach ($build->objects as $property_name => $value) {
			$property = $this->properties[$property_name];
			if (!$this->buildSubObject($build->object, $property, $value, $build->null_if_empty)) {
				$build->is_null = false;
			}
		}
	}

	//------------------------------------------------------------------------------- extractAsterisk
	/**
	 * @param $property_name string may end with a '*' : if so, this last character will be removed
	 * @return boolean true if there is an '*', false if not
	 */
	private function extractAsterisk(&$property_name)
	{
		if ($asterisk = (substr($property_name, -1) === '*')) {
			$property_name = substr($property_name, 0, -1);
		}
		return $asterisk;
	}

	//------------------------------------------------------------------------------- getBuiltObjects
	/**
	 * Call this after calls to build() to get all objects list set by the built
	 *
	 * @return object[]
	 */
	public function getBuiltObjects()
	{
		return $this->built_objects;
	}

	//-------------------------------------------------------------------------------- initLinkObject
	/**
	 * @param $array
	 * @param $object
	 * @return array
	 */
	private function initLinkObject(&$array, &$object)
	{
		/** @var $link Class_\Link_Annotation */
		$link = $this->class->getAnnotation('link');
		if ($link->value) {
			$id_property_value = null;
			$linked_class_name = null;
			$link_properties = $link->getLinkProperties();
			$search = [];
			foreach ($link_properties as $property) {
				if ($property->getType()->isClass()) {
					$property_name = $property->getName();
					$id_property_name = 'id_' . $property_name;
					if (isset($array[$id_property_name]) && $array[$id_property_name]) {
						$search[$property_name] = $array[$id_property_name];
					}
					$property_class_name = $property->getType()->asString();
					if (is_a($property_class_name, $link->value, true)) {
						$id_property_value = isset($array[$id_property_name])
							? $array[$id_property_name] : null;
						$linked_class_name = $property_class_name;
						if (!isset($array[$id_property_name]) && !isset($array[$property_name])) {
							$linked_array = $array;
							foreach (array_keys($link_properties) as $link_property_name) {
								unset($linked_array[$link_property_name]);
							}
							$array[$property_name] = (new Object_Builder_Array($property_class_name))->build(
								$linked_array
							);
						}
					}
				}
			}
			if (count($search) >= 2) {
				$object = Dao::searchOne($search, $this->class->name);
			}
			if ($id_property_value && !$object) {
				$object = Builder::createClone(
					Dao::read($id_property_value, $linked_class_name), $this->class->name
				);
			}
			return $search;
		}
		return null;
	}

	//------------------------------------------------------------------------------------ initObject
	/**
	 * Initializes the object if not already set
	 * - if a data link identifier is set, read the object from the data link and remove it from
	 *   $array
	 * - if the object is a link class and the link class identifier properties values are set,
	 *   read the object from the data link
	 *
	 * @param $array  array  the source array
	 * @param $object object the object to complete (if set) or to build (if null)
	 *                This object is always set at the end of execution of initObject()
	 * @return array if read from a link object, this is the search properties that identify it
	 */
	private function initObject(&$array, &$object)
	{
		if (!isset($object)) {
			if (isset($array['id']) && $array['id']) {
				$object = Dao::read($array['id'], $this->class->name);
			}
			else {
				foreach ($this->class->getAnnotations('before_build_array') as $before) {
					call_user_func_array([$this->class->name, $before->value], [&$array]);
				}
				$link_search = $this->initLinkObject($array, $object);
				if (!isset($object)) {
					$object = $this->class->newInstance();
				}
			}
			if (isset($array['id'])) {
				unset($array['id']);
			}
		}
		return isset($link_search) ? $link_search : null;
	}

	//------------------------------------------------------------------------------------ readObject
	/**
	 *
	 * @param $object          object
	 * @param $read_properties string[] properties names
	 * @return object
	 */
	public function readObject($object, $read_properties)
	{
		$objects = Dao::search($read_properties, get_class($object));
		if (count($objects) > 1) {
			trigger_error(
				'Unique object not found' . SP . get_class($object) . SP . print_r($read_properties, true),
				E_USER_ERROR
			);
		}
		elseif ($objects) {
			$new_object = reset($objects);
			foreach ((new Reflection_Class(get_class($object)))->accessProperties() as $property) {
				$property_name = $property->name;
				if (isset($object->$property_name) && !isset($read_properties[$property->name])) {
					$property->setValue($new_object, $property->getValue($object));
				}
			}
			$object = $new_object;
		}
		return $object;
	}

	//-------------------------------------------------------------------------------------- setClass
	/**
	 * @param $class_name string
	 */
	public function setClass($class_name)
	{
		if ($this->started) {
			$this->stop();
		}
		$this->class = new Reflection_Class(Builder::className($class_name));
		$this->defaults = $this->class->getDefaultProperties();
	}

	//----------------------------------------------------------------------------------------- start
	/**
	 * @param $class_name string
	 */
	public function start($class_name = null)
	{
		if (isset($class_name)) {
			$this->setClass($class_name);
		}
		elseif ($this->started) {
			$this->stop();
		}
		$this->built_objects = [];
		$this->properties = $this->class->accessProperties();
		$this->started = true;
	}

	//------------------------------------------------------------------------------------------ stop
	public function stop()
	{
		$this->started = false;
	}

}
