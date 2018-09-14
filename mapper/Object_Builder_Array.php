<?php
namespace ITRocks\Framework\Mapper;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Property\Encrypt_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Null_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Password_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Widget_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Password;
use ITRocks\Framework\View\Html\Builder\Property;

/**
 * Build an object and it's property values from data stored into a recursive array
 *
 * TODO LOW Do we need to do if (!isset($object->$property_name)) into all builders ? Please check.
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
	 * @var Built_Object[]
	 */
	private $built_objects;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Reflection_Class
	 */
	private $class;

	//------------------------------------------------------------------------------------ $composite
	/**
	 * Store composite object to attach the @composite property of a Component built object
	 *
	 * @var object
	 */
	public $composite = null;

	//------------------------------------------------------------------------------------ $from_form
	/**
	 * True (default) if apply build specifics for arrays that come from an input form :
	 * - apply arrayFormRevert to split key positions
	 * - apply widgets
	 * Setting this to false disable these specific processes.
	 *
	 * @var boolean
	 */
	private $from_form;

	//-------------------------------------------------------------------- $ignore_unknown_properties
	/**
	 * If false, build() will generate an error if the array contains data for properties that do not
	 * exist in object's class.
	 * With true, you do not generate this error but we ignore unknown properties
	 * With null, we store unknown properties into the object
	 *
	 * @var boolean|null
	 */
	public $ignore_unknown_properties = false;

	//-------------------------------------------------------------------- $null_if_empty_sub_objects
	/**
	 * @var boolean set sub-objects null if empty, even if main object accepts null if empty
	 */
	public $null_if_empty_sub_objects = false;

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
	 * @param $composite  object|null Reference to the composite object if we build a Component
	 */
	public function __construct($class_name = null, $from_form = true, $composite = null)
	{
		$this->from_form = $from_form;
		$this->composite = $composite;
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
		array $array, $object = null, $null_if_empty = false, $ignore_property_name = null
	) {
		if (!$this->started) {
			$this->start(isset($object) ? get_class($object) : null);
		}
		$search = $this->initObject($array, $object);
		$build  = new Object_Builder_Array_Tool(
			$array, $object, $null_if_empty, $ignore_property_name, $search
		);
		$this->buildProperties($build);
		$this->buildSubObjects($build);
		if ($build->is_null && count($array)) {
			return null;
		}
		else {
			if ($build->read_properties) {
				$object = $this->readObject($object, $build->read_properties);
			}
			$this->built_objects[] = new Built_Object($build->object);
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
		if (!is_null($value) || !Null_Annotation::of($property)->value) {
			if (is_string($value)) {
				$value = trim($value);
			}
			switch ($property->getType()->asString()) {
				case Type::BOOLEAN:
					$value = !(empty($value) || ($value === _FALSE));
					break;
				case Type::INTEGER:
					$value = isStrictInteger($value) ? intval($value) : $value;
					break;
				case Type::FLOAT:
					$value = isStrictNumeric($value) ? floatval($value) : $value;
					break;
			}
		}
		return $value;
	}

	//------------------------------------------------------------------------------- buildCollection
	/**
	 * Accepted arrays :
	 * $array[$object_number][$property_name] = $value
	 * $array[$property_name][$object_number] = $value
	 * $array[0][$column_number] = 'property_name' then $array[$object_number][$column_number] = $value
	 *
	 * @param $class_name    string
	 * @param $array         array
	 * @param $null_if_empty boolean
	 * @param $composite     object the composite object, if linked
	 * @return object[]
	 */
	public function buildCollection(
		$class_name, array $array, $null_if_empty = false, $composite = null
	) {
		$collection = [];
		if ($array) {
			$builder = new Object_Builder_Array($class_name, $this->from_form, $composite);
			// replace $array[$property_name][$object_number] with $array[$object_number][$property_name]
			reset($array);
			if ($this->from_form && !is_numeric(key($array))) {
				$array = arrayFormRevert($array, false);
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
				$object = $builder->build(
					$element, null, $this->null_if_empty_sub_objects || $null_if_empty
				);
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
	private function buildDottedProperty(
		Object_Builder_Array_Tool $build, $property_name, $value, $pos
	) {
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
		$is_null            = $null_if_empty;
		$real_property_name = substr($property_name, 3);
		$property           = $this->properties[$real_property_name];
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
		// forces the call to the AOP / setter, if there is one for the property
		if ($value && (!isset($object->$property_name) || ($value != $object->$property_name))) {
			$property = new Reflection_Property(get_class($object), $real_property_name);
			/*
			// Evolution proposal, but not freshly tested (and not enough time to do this)
			$GLOBALS['D'] = true;
			if (isset($object->_[$real_property_name])) {
			*/
			if ($property->getAnnotation('setter')->value) {
				$dao                         = Dao::get($property->getAnnotation('dao')->value);
				$object->$real_property_name = $dao->read($value, $property->getType()->asString());
			}
		}
		if (!isset($object->$property_name) || ($value != $object->$property_name)) {
			$object->$property_name = $value;
		}
		if (!$property->isValueEmptyOrDefault($value)) {
			$is_null = false;
		}
		return $is_null;
	}

	//-------------------------------------------------------------------------------------- buildMap
	/**
	 * @param $array      array
	 * @param $class_name string the name of the class to build each element
	 * @return integer[]
	 */
	public function buildMap(array $array, $class_name)
	{
		$map = [];
		// file identifiers are copied to array values
		if (isset($array['id'])) {
			foreach ($array['id'] as $key => $identifier) {
				if (!$array[$key]) {
					$array[$key] = $identifier;
				}
			}
			unset($array['id']);
		}
		// build each element
		foreach ($array as $key => $element) {
			if (!empty($element)) {
				if (is_array($element)) {
					$map[$key] = (new Object_Builder_Array($class_name, $this->from_form))->build(
						$element, null, true
					);
				}
				else {
					$map[$key] = is_object($element) ? $element : Dao::read($element, $class_name);
				}
			}
		}
		return $map;
	}

	//------------------------------------------------------------------------------ buildObjectValue
	/**
	 * @param $class_name    string the class name of the object to build
	 * @param $object        object the value of the object before build (may be null if no object)
	 * @param $array         array  the values of the properties to be replaced into the object
	 * @param $null_if_empty boolean
	 * @param $composite     object The composite object (set it only if property is a @component)
	 * @return object
	 */
	private function buildObjectValue($class_name, $object, array $array, $null_if_empty, $composite)
	{
		$builder = new Object_Builder_Array($class_name, $this->from_form, $composite);
		$object  = $builder->build($array, $object, $this->null_if_empty_sub_objects || $null_if_empty)
			?: $object;
		if ($object && $composite && isA($class_name, Component::class)) {
			array_pop($builder->built_objects);
		}
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
			&& ($builder = Widget_Annotation::of($property)->value)
			&& is_a($builder, Property::class, true)
		) {
			$builder = Builder::create($builder, [$property, $value]);
			/** @var $builder Property */
			$value2 = $builder->buildValue($object, $null_if_empty);
			if ($value2 !== Property::DONT_BUILD_VALUE) {
				$value = $value2;
				$done  = true;
			}
		}
		if (!isset($done)) {
			$type = $property->getType();
			if ($type->isBasic(false)) {
				// password
				if ($encryption = (
					Encrypt_Annotation::of($property)->value ?: Password_Annotation::of($property)->value
				)) {
					if ($value === Password::UNCHANGED) {
						return true;
					}
					$value = (new Password($value, $encryption))->encrypted();
					if ($value === Password::UNCHANGED) {
						return true;
					}
				}
				// others basic values
				else {
					$value = $this->buildBasicValue($property, $value);
				}
			}
			elseif (is_array($value)) {
				$link = Link_Annotation::of($property);
				// object
				if ($link->isObject()) {
					$class_name       = $property->getType()->asString();
					$composite_object = $property->getAnnotation('component')->value ? $object : null;
					$value            = $this->buildObjectValue(
						$class_name, $property->getValue($object), $value, $null_if_empty, $composite_object
					);
				}
				// collection
				elseif ($link->isCollection()) {
					$class_name = $property->getType()->getElementTypeAsString();
					$value      = $this->buildCollection($class_name, $value, $null_if_empty, $object);
				}
				// map or not-linked array of objects
				elseif ($property->getType()->isClass()) {
					$value = $this->buildMap($value, $property->getType()->getElementTypeAsString());
				}
			}
			// @output string
			elseif (isset($value) && ($property->getAnnotation('output')->value == 'string')) {
				$value = call_user_func([$property->getType()->asString(), 'fromString'], trim($value));
			}
		}
		// the property value is set only for official properties, if not default and not empty
		$property_name = $property->name;
		if (($value !== '') || !$property->getType()->isClass()) {
			if (!isset($object->$property_name) || ($value != $object->$property_name)) {
				$object->$property_name = $value;
			}
		}
		if (
			$property->getAnnotation('empty_check')->value
			&& !$property->isValueEmptyOrDefault($value)
		) {
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
			if (is_null($this->ignore_unknown_properties)) {
				$build->object->$property_name = $value;
			}
			elseif (!$this->ignore_unknown_properties) {
				trigger_error(
					'Unknown property ' . $this->class->name . '::$' . $property_name, E_USER_ERROR
				);
			}
		}
		elseif (!(
			$property && $this->buildProperty($build->object, $property, $value, $build->null_if_empty)
		)) {
			if (!$property || $property->getAnnotation('empty_check')->value) {
				$build->is_null = false;
			}
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
		$is_null       = $null_if_empty;
		$property_name = $property->name;
		$type          = $property->getType();
		if (!isset($this->builders[$property_name])) {
			$this->builders[$property_name] = new Object_Builder_Array(
				$type->getElementTypeAsString(), $this->from_form
			);
		}
		$builder = $this->builders[$property_name];
		if ($type->isMultiple()) {
			$is_null = $this->buildSubObjectMultiple(
				$object, $property_name, $value, $null_if_empty, $builder
			);
		}
		else {
			$sub_object = $property->getValue($object);
			$value      = $builder->build($value, $sub_object, $null_if_empty) ?: $sub_object;
			if (isset($value)) {
				$object->$property_name = $value;
				$is_null                = false;
			}
		}
		return $is_null;
	}

	//------------------------------------------------------------------------ buildSubObjectMultiple
	/**
	 * @param $builder       Object_Builder_Array
	 * @param $null_if_empty boolean
	 * @param $object        object
	 * @param $property_name string
	 * @param $value         mixed
	 * @return boolean
	 */
	private function buildSubObjectMultiple(
		$object, $property_name, $value, $null_if_empty, Object_Builder_Array $builder
	) {
		$is_null = $null_if_empty;
		if (is_array($value)) {
			// keys are numeric : multiple values case
			foreach ($value as $key => $element) {
				if (is_numeric($number = lParse($key, DOT, 1, false))) {
					$values[$number][rParse($key, DOT)] = $element;
				}
				else {
					unset($values);
					break;
				}
			}
			// single value case
			if (!isset($values)) {
				$values = [$value];
			}
			// build values
			foreach ($values as $element) {
				$element = $builder->build($element, null, $null_if_empty);
				if (isset($element)) {
					// call property getter if exist (do not remove this !)
					$object->$property_name;
					array_push($object->$property_name, $element);
					$is_null = false;
				}
			}
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
	 * @return Built_Object[]
	 */
	public function getBuiltObjects()
	{
		return $this->built_objects;
	}

	//-------------------------------------------------------------------------------- initLinkObject
	/**
	 * @param $array  array
	 * @param $object object
	 * @return array
	 */
	private function initLinkObject(array &$array, &$object)
	{
		$link = Class_\Link_Annotation::of($this->class);
		if ($link->value) {
			$id_property_value = null;
			$linked_class_name = null;
			$link_properties   = $link->getLinkClass()->getUniqueProperties();
			$search            = [];
			foreach ($link_properties as $property) {
				$property_name = $property->getName();
				if (Dao::storedAsForeign($property)) {
					$id_property_name = 'id_' . $property_name;
					if (isset($array[$id_property_name])) {
						$search[$property_name] = $array[$id_property_name] ?: null;
					}
					$property_class_name = $property->getType()->asString();
					if (is_a($property_class_name, $link->value, true)) {
						$id_property_value = isset($array[$id_property_name])
							? $array[$id_property_name]
							: null;
						$linked_class_name = $property_class_name;
						if (!isset($array[$id_property_name]) && !isset($array[$property_name])) {
							$linked_array = $array;
							foreach (array_keys($link_properties) as $link_property_name) {
								unset($linked_array[$link_property_name]);
								unset($linked_array['id_' . $link_property_name]);
							}
							$link_class_properties = $link->getLinkClass()->getLocalProperties();
							foreach (array_keys($link_class_properties) as $link_property_name) {
								unset($linked_array[$link_property_name]);
								unset($linked_array['id_' . $link_property_name]);
							}
							if ($linked_array) {
								$builder = new Object_Builder_Array($property_class_name, $this->from_form);
								$array[$property_name] = $builder->build($linked_array);
							}
						}
					}
				}
				elseif (isset($array[$property_name])) {
					$search[$property_name] = $property->getType()->isDateTime()
						? Loc::dateToIso($array[$property_name])
						: $array[$property_name];
				}
			}
			if (count($search) >= count($link_properties)) {
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
	 * - if the object is a Component with a known composite, initializes its composite object to
	 *   $this->composite
	 *
	 * @param $array  array  the source array
	 * @param $object object the object to complete (if set) or to build (if null)
	 *                This object is always set at the end of execution of initObject()
	 * @return array if read from a link object, this is the search properties that identify it
	 */
	private function initObject(array &$array, &$object)
	{
		if (!isset($object)) {
			if (isset($array['id']) && $array['id']) {
				$object = Dao::read($array['id'], $this->class->name);
				// if the object has been removed from the database : we will create a new one
				if (!$object) {
					unset($array['id']);
				}
			}
			if (!(isset($array['id']) && $array['id'])) {
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
		if ($this->composite && isA($object, Component::class)) {
			$object->setComposite($this->composite);
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
	public function readObject($object, array $read_properties)
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
			/** @noinspection PhpUnhandledExceptionInspection Class of an object is always valid */
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
		$this->properties    = $this->class->accessProperties();
		$this->started       = true;
	}

	//------------------------------------------------------------------------------------------ stop
	public function stop()
	{
		$this->started = false;
	}

}
