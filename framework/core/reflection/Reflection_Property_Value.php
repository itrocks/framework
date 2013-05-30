<?php
namespace SAF\Framework;

use ReflectionClass;
use ReflectionProperty;

/**
 * A reflection property value is a reflection property enriched with it's display label and a value
 */
class Reflection_Property_Value extends Reflection_Property
{

	//-------------------------------------------------------------------------------------- $display
	/**
	 * What will be displayed by the display() function
	 *
	 * Keep this null to calculate automatically, fill this only to force display
	 *
	 * @var string|null
	 */
	public $display = null;

	//---------------------------------------------------------------------------------- $final_value
	/**
	 * If set to true, $object contains the final value instead of the object containing the valued property
	 *
	 * @var boolean
	 */
	private $final_value;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * The object ($final_value = false) or the value ($final_value = true) of the property
	 *
	 * @var object
	 */
	private $object;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a reflection property with value
	 *
	 * @example
	 * $pv = new Reflection_Property_Value($object, "property_name");
	 * $pv = new Reflection_Property_Value("Class_Name", "property_name", $object);
	 * $pv = new Reflection_Property_Value($reflection_property, $object);
	 * $pv = new Reflection_Property_Value($reflection_property, $value);
	 *
	 * @param $class       string|ReflectionClass|Reflection_Class|ReflectionProperty|Reflection_Property|object
	 * @param $name        string|ReflectionProperty|Reflection_Property
	 * @param $object      object|mixed the object containing the value, or the value itself (in this case set $final_value tu true)
	 * @param $final_value boolean set to true if $object is a final value instead of the object containing the valued property
	 */
	public function __construct($class, $name = null, $object = null, $final_value = false)
	{
		if ($class instanceof ReflectionClass) {
			$class = $name->class;
		}
		elseif ($class instanceof ReflectionProperty) {
			if (isset($name) && !isset($object)) {
				$object = $name;
			}
			$name = $class->name;
			$class = $class->class;
		}
		elseif (is_object($class)) {
			if (!isset($object)) {
				$object = $class;
			}
			$class = get_class($class);
		}
		if ($name instanceof ReflectionProperty) {
			$class = $name->class;
			$name = $name->name;
		}
		if (strpos($name, ".")) {
			$model = Reflection_Property::getInstanceOf($class, $name);
			parent::__construct($model->class, $model->name);
		}
		else {
			parent::__construct($class, $name);
		}
		$this->getAdditionalProperties();
		$this->final_value = $final_value;
		$this->path = $name;
		if (!isset($this->object)) {
			$this->object = $object;
		}
	}

	//----------------------------------------------------------------------------------------- __get
	/**
	 * Sets additional properties to matching Reflection_Property (common for all instances of this property)
	 *
	 * @param $key string
	 * @return mixed
	 */
	public function __get($key)
	{
		$property = Reflection_Property::getInstanceOf($this->class, $this->name);
		return isset($property->$key) ? $property->$key : null;
	}

	//----------------------------------------------------------------------------------------- __set
	/**
	 * Sets additional properties to matching Reflection_Property (common for all instances of this property)
	 *
	 * @param $key   string
	 * @param $value mixed
	 */
	public function __set($key, $value)
	{
		Reflection_Property::getInstanceOf($this->class, $this->name)->$key = $value;
	}

	//--------------------------------------------------------------------------------------- display
	/**
	 * @return string
	 */
	public function display()
	{
		return $this->display
			? $this->display
			: Names::propertyToDisplay($this->path ? $this->path : $this->name);
	}

	//----------------------------------------------------------------------------------------- field
	/**
	 * Returns path formatted as field : uses [] instead of .
	 *
	 * @example if $this->path is "a.field.path", will return "a[field][path]"
	 * @return string
	 */
	public function field()
	{
		return Names::propertyPathToField($this->path ? $this->path : $this->name);
	}

	//---------------------------------------------------------------------------------------- format
	/**
	 * @return mixed
	 */
	public function format()
	{
		return (new Reflection_Property_View($this))->getFormattedValue($this->object);
	}

	//----------------------------------------------------------------------- getAdditionalProperties
	/**
	 * Reads additional properties from the matching Reflection_Property
	 */
	private function getAdditionalProperties()
	{
		$property = Reflection_Property::getInstanceOf($this->class, $this->name);
		foreach (get_object_vars($property) as $key => $value) {
			if (($key != "class") && ($key != "name")) {
				$this->$key = $value;
			}
		}
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Gets the object containing the value (null if the value was set as a value)
	 *
	 * @return object|null
	 */
	public function getObject()
	{
		return $this->object;
	}

	//----------------------------------------------------------------------------------------- value
	/**
	 * @param null $value object
	 * @return mixed
	 */
	public function value($value = null)
	{
		if ($value !== null) {
			if ($this->final_value) {
				$this->object = $value;
			}
			else {
				$this->setValue($this->object, $value);
			}
		}
		return $this->final_value ? $this->object : $this->getValue($this->object);
	}

}
