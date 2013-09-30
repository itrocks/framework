<?php
namespace SAF\Framework;

use Serializable;

/**
 * Import class
 */
class Import_Class implements Serializable
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//------------------------------------------------------------------------------------ $constants
	/**
	 * @var Reflection_Property_Value[] key is the name of the property
	 */
	public $constants = array();

	//-------------------------------------------------------------------------------- $property_path
	/**
	 * @var string[]
	 */
	public $property_path;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//------------------------------------------------------------------- $object_not_found_behaviour
	/**
	 * @var string
	 * @values create_new_value, do_nothing, tell_it_and_stop_import
	 */
	public $object_not_found_behaviour = "do_nothing";

	//-------------------------------------------------------------------------- $identify_properties
	/**
	 * @var Import_Property[]
	 */
	public $identify_properties = array();

	//---------------------------------------------------------------------------- $ignore_properties
	/**
	 * @var Import_Property[]
	 */
	public $ignore_properties = array();

	//----------------------------------------------------------------------------- $write_properties
	/**
	 * @var Import_Property[]
	 */
	public $write_properties = array();

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name                 string
	 * @param $property_path              string[]
	 * @param $object_not_found_behaviour string create_new_value, do_nothing, tell_it_and_stop_import
	 */
	public function __construct(
		$class_name = null, $property_path = null, $object_not_found_behaviour = null
	) {
		if (isset($class_name)) {
			$this->class_name = $class_name;
			$this->name = Names::classToDisplay($class_name);
		}
		if (isset($object_not_found_behaviour)) {
			$this->object_not_found_behaviour = $object_not_found_behaviour;
		}
		if (isset($property_path)) {
			$this->property_path = $property_path;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->getPropertyPathValue();
	}

	//------------------------------------------------------------------------------ getIdentifyValue
	/**
	 * @return string
	 */
	public function getIdentifyValue()
	{
		$properties = array();
		foreach ($this->identify_properties as $property) {
			$properties[] = $property->name;
		}
		return join(",", $properties);
	}

	//-------------------------------------------------------------------------------- getIgnoreValue
	/**
	 * @return string
	 */
	public function getIgnoreValue()
	{
		$properties = array();
		foreach ($this->ignore_properties as $property) {
			$properties[] = $property->name;
		}
		return join(",", $properties);
	}

	//-------------------------------------------------------------------------- getPropertyPathValue
	/**
	 * @return string
	 */
	public function getPropertyPathValue()
	{
		return $this->property_path
			? (join(".", $this->property_path))
			: Namespaces::shortClassName($this->class_name);
	}

	//--------------------------------------------------------------------------------- getWriteValue
	/**
	 * @return string
	 */
	public function getWriteValue()
	{
		$properties = array();
		foreach ($this->write_properties as $property) {
			$properties[] = $property->name;
		}
		return join(",", $properties);
	}

	//------------------------------------------------------------------------------------- serialize
	/**
	 * @return string
	 */
	public function serialize()
	{
		$serialize = get_object_vars($this);
		if (isset($serialize["constants"]) && is_array($serialize["constants"])) {
			/** @var $value Reflection_Property_Value */
			foreach ($serialize["constants"] as $key => $value) {
				$serialize["constants"][$key] = array(
					"class" => $value->class, "name" => $value->name, "value" => $value->value(),
					"final_object" => true
				);
			}
		}
		return serialize($serialize);
	}

	//----------------------------------------------------------------------------------- unserialize
	/**
	 * @param $serialized string
	 */
	public function unserialize($serialized)
	{
		foreach (unserialize($serialized) as $key => $value) {
			if ($key == "constants") {
				foreach ($value as $constant_key => $constant_value) {
					$this->constants[$constant_key] = new Reflection_Property_Value(
						$constant_value["class"],
						$constant_value["name"],
						$constant_value["value"],
						$constant_value["final_object"]
					);
				}
			}
			else {
				$this->$key = $value;
			}
		}
	}

}
