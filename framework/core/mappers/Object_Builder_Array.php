<?php
namespace SAF\Framework;

/**
 * Build an object and it's property values from data stored into a recursive array
 */
class Object_Builder_Array
{

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
	 * @param $array         array
	 * @param $object        object
	 * @param $null_if_empty boolean
	 * @return object
	 */
	public function build($array, $object = null, $null_if_empty = false)
	{
		$is_null = $null_if_empty;
		if (!$this->started) {
			$this->start(isset($object) ? get_class($object) : null);
		}
		$properties = $this->properties;
		if (!isset($object)) {
			$object = $array["id"]
				? Dao::read($array["id"], $this->class->name)
				: $this->class->newInstance();
		}
		foreach ($array as $property_name => $value) {
			$property = isset($properties[$property_name]) ? $properties[$property_name] : null;
			$type = isset($property) ? $property->getType() : null;
			if (isset($type)) {
				if ($type->isBasic()) {
					$value = $this->buildBasicValue($property, $value);
				}
				elseif (is_array($value)) {
					$link = $property->getAnnotation("link")->value;
					if ($link == "Object") {
						$value = $this->buildObjectValue($property, $value, $null_if_empty);
					}
					elseif ($link == "Collection") {
						$value = $this->buildCollectionValue($property, $value, $null_if_empty);
					}
					elseif ($link == "Map") {
						$value = $this->buildMapValue($property, $value, $null_if_empty);
					}
				}
			}
			// the property value is set only for official properties, if not default and not empty
			$object->$property_name = $value;
			if (isset($property) && !$property->isValueEmptyOrDefault($value)) {
				$is_null = false;
			}
			// if an id_foo property is set and not empty, it can be set and associated object is removed
			// id_foo must always be set before any forced foo[sub_property] values into the array
			if (!isset($property) && (substr($property_name, 0, 3) == "id_")) {
				if (empty($value)) {
					$value = 0;
				}
				$object->$property_name = $value;
				if ($value) {
					$linked_name = substr($property_name, 3);
					unset($object->$linked_name);
					$is_null = false;
				}
			}
		}
		if ($is_null) {
			return null;
		}
		else {
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
			case "boolean": $value = !(empty($value) || ($value === "false")); break;
			case "integer": $value = intval($value);                           break;
			case "float":   $value = floatval($value);                         break;
		}
		return $value;
	}

	//-------------------------------------------------------------------------- buildCollectionValue
	/**
	 * @param $property      Reflection_Property
	 * @param $array         array
	 * @param $null_if_empty boolean
	 * @return object[]
	 */
	private function buildCollectionValue(Reflection_Property $property, $array, $null_if_empty)
	{
		$collection = array();
		if ($array) {
			$builder = new Object_Builder_Array($property->getType()->getElementTypeAsString());
			reset($array);
			if (!is_numeric(key($array))) {
				$array = arrayFormRevert($array);
			}
			foreach ($array as $key => $element) {
				$object = $builder->build($element, null, $null_if_empty);
				if (isset($object)) {
					$collection[$key] = $object;
				}
			}
		}
		return $collection;
	}

	//--------------------------------------------------------------------------------- buildMapValue
	/**
	 * @param $property      Reflection_Property
	 * @param $array         array
	 * @param $null_if_empty boolean
	 * @return integer[]
	 */
	private function buildMapValue(
		/** @noinspection PhpUnusedParameterInspection */
		Reflection_Property $property, $array, $null_if_empty
	) {
		$map = array();
		if ($array) {
			foreach ($array as $key => $element) {
				if (!empty($element)) {
					$map[$key] = intval($element);
				}
			}
		}
		return $map;
	}

	//------------------------------------------------------------------------------ buildObjectValue
	/**
	 * @param $property      Reflection_Property
	 * @param $array         array
	 * @param $null_if_empty boolean
	 * @return object
	 */
	private function buildObjectValue(Reflection_Property $property, $array, $null_if_empty)
	{
		$builder = new Object_Builder_Array($property->getType()->asString());
		$object = $builder->build($array, null, $null_if_empty);
		$this->built_objects = array_merge($this->built_objects, $builder->built_objects);
		return $object;
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
		$this->built_objects = array();
		$this->properties = $this->class->accessProperties();
		$this->started = true;
	}

	//------------------------------------------------------------------------------------------ stop
	public function stop()
	{
		$this->class->accessPropertiesDone();
		$this->started = false;
	}

}
