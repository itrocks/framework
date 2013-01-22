<?php
namespace SAF\Framework;
use ReflectionClass;
use ReflectionProperty;

class Reflection_Property_Value extends Reflection_Property
{

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	public $object;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a reflection property with value
	 *
	 * @example
	 * $pv = new Reflection_Property_Value($object, "property_name");
	 * $pv = new Reflection_Property_Value("Class_Name", "property_name", $object);
	 * $pv = new Reflection_Property_Value($reflection_property, $object);
	 *
	 * @param string|ReflectionClass|Reflection_Class|ReflectionProperty|Reflection_Property|object $class
	 * @param string|ReflectionProperty|Reflection_Property $name
	 * @param object $object
	 */
	public function __construct($class, $name = null, $object = null)
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
		parent::__construct($class, $name);
		$this->getAdditionalProperties();
		$this->object = $object;
	}

	//--------------------------------------------------------------------------------------- display
	/**
	 * @return string
	 */
	public function display()
	{
		return Names::propertyToDisplay($this->name);
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

	//----------------------------------------------------------------------------------------- value
	/**
	 * @return mixed
	 */
	public function value()
	{
		return $this->getValue($this->object);
	}

}
