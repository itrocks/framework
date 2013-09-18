<?php
namespace SAF\Framework;

/**
 * Import class
 */
class Import_Class
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//--------------------------------------------------------------------------- $class_path_display
	/**
	 * @var string[]
	 */
	public $class_path_display;

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
	 * @param $object_not_found_behaviour string create_new_value, do_nothing, tell_it_and_stop_import
	 * @param $class_path_display         string[]
	 */
	public function __construct(
		$class_name = null, $object_not_found_behaviour = null, $class_path_display = null
	) {
		if (isset($class_name)) {
			$this->class_name = $class_name;
			$this->name = Names::classToDisplay($class_name);
		}
		if (isset($object_not_found_behaviour)) {
			$this->object_not_found_behaviour = $object_not_found_behaviour;
		}
		if (isset($class_path_display)) {
			$this->class_path_display = $class_path_display;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->class_name);
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

}
