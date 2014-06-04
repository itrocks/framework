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

	//----------------------------------------------------------------------------------- $properties
	/**
	 * Properties list, set by start()
	 *
	 * @var Reflection_Property[]
	 */
	private $properties;

	//-------------------------------------------------------------------------------------- $started
	/**
	 * @var boolean
	 */
	private $started = false;

	//-------------------------------------------------------------------------------- $built_objects
	/**
	 * @var array
	 */
	private $built_objects;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 */
	public function __construct($class_name = null)
	{
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
	 * TODO LOW This method is too much big : please refactor it
	 *
	 * @param $array                array
	 * @param $object               object
	 * @param $null_if_empty        boolean
	 * @param $ignore_property_name string
	 * @return object
	 */
	public function build(
		$array, $object = null, $null_if_empty = false, $ignore_property_name = null
	) {
		$is_null = $null_if_empty;
		if (!$this->started) {
			$this->start(isset($object) ? get_class($object) : null);
		}
		$properties = $this->properties;
		if (!isset($object)) {
			if (isset($array['id']) && $array['id']) {
				$object = Dao::read($array['id'], $this->class->name);
			}
			else {
				foreach ($this->class->getAnnotations('before_build_array') as $before) {
					call_user_func_array([$this->class->name, $before->value], [&$array]);
				}
				/** @var $link Class_\Link_Annotation */
				$link = $this->class->getAnnotation('link');
				if ($link->value) {
					$id_property_value = null;
					$linked_class_name = null;
					$search = [];
					foreach ($link->getLinkProperties() as $property_name) {
						$id_property_name = 'id_' . $property_name;
						if (isset($array[$id_property_name]) && $array[$id_property_name]) {
							$search[$property_name] = $array[$id_property_name];
						}
						$property_class_name = $this->class->getProperty($property_name)->getType()->asString();
						if (is_a($property_class_name, $link->value, true)) {
							$id_property_value = $array[$id_property_name];
							$linked_class_name = $property_class_name;
						}
					}
					if (count($search) >= 2) {
						$object = Dao::searchOne($search, $this->class->name);
						if (!$object && $id_property_value) {
							$object = Builder::createClone(
								Dao::read($id_property_value, $linked_class_name), $this->class->name
							);
						}
					}
				}
				if (!isset($object)) {
					$object = $this->class->newInstance();
				}
			}
			if (isset($array['id'])) {
				unset($array['id']);
			}
		}
		$objects = [];
		$read_properties = [];
		foreach ($array as $property_name => $value) {
			if ($pos = strpos($property_name, DOT)) {
				$property_path = substr($property_name, $pos + 1);
				$property_name = substr($property_name, 0, $pos);
				if ($asterisk = (substr($property_name, -1) === '*')) {
					$property_name = substr($property_name, 0, -1);
				}
				$property = isset($properties[$property_name]) ? $properties[$property_name] : null;
				if (isset($property)) {
					$objects[$property->name][$property_path] = $value;
				}
			}
			else {
				if ($asterisk = (substr($property_name, -1) === '*')) {
					$property_name = substr($property_name, 0, -1);
				}
				$property = isset($properties[$property_name]) ? $properties[$property_name] : null;
				if (substr($property_name, 0, 3) === 'id_') {
					if (
						!$this->buildIdProperty($object, $property_name, $value, $null_if_empty)
						&& (
							($ignore_property_name === $property_name)
							|| !isset($search[substr($property_name, 3)])
						)
					) {
						$is_null = false;
					}
				}
				elseif (($property_name != 'id') && !isset($property)) {
					trigger_error(
						'Unknown property ' . $property_name . ' into ' . $this->class->name, E_USER_ERROR
					);
				}
				elseif (!$this->buildProperty($object, $property, $value, $null_if_empty)) {
					$is_null = false;
				}
				if ($asterisk) {
					$read_properties[$property_name] = $value;
				}
			}
		}
		foreach ($objects as $property_name => $value) {
			$property = $properties[$property_name];
			if (!$this->buildSubObject($object, $property, $value, $null_if_empty)) {
				$is_null = false;
			}
		}
		if ($is_null) {
			return null;
		}
		else {
			if ($read_properties) {
				$object = $this->readObject($object, $read_properties);
			}
			$this->built_objects[] = $object;
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
		switch ($property->getType()->asString()) {
			case Type::BOOLEAN: $value = !(empty($value) || ($value === 'false')); break;
			case Type::INTEGER: $value = intval($value);                           break;
			case Type::FLOAT:   $value = floatval($value);                         break;
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
			if (!is_numeric(key($array))) {
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
		if (empty($value)) {
			$value = $this->properties[$real_property_name]->getAnnotation('null')->value ? null : 0;
		}
		$object->$property_name = $value;
		$object->$real_property_name = null;
		if ($value) {
			$is_null = false;
		}
		return $is_null;
	}

	//--------------------------------------------------------------------------------- buildMapValue
	/**
	 * @param $array      array
	 * @param $class_name string if set and element is not an object, will read
	 * @return integer[]
	 */
	private function buildMap($array, $class_name = null)
	{
		$map = [];
		if ($array) {
			foreach ($array as $key => $element) {
				if (!empty($element)) {
					$map[$key] = is_object($element)
						? $element
						: (isset($class_name) ? Dao::read($element, $class_name) : intval($element));
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
				$value = $this->buildMap(
					$value,
					($link == Link_Annotation::MAP) ? null : $property->getType()->getElementTypeAsString()
				);
			}
		}
		// the property value is set only for official properties, if not default and not empty
		$property_name = $property->name;
		$object->$property_name = $value;
		if (!$property->isValueEmptyOrDefault($value)) {
			$is_null = false;
		}
		return $is_null;
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
