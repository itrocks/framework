<?php
namespace SAF\Framework\Import\Settings;

/**
 * Import settings
 */
class Import_Settings extends Custom_Settings
{

	//-------------------------------------------------------------------------------------- $classes
	/**
	 * @var Import_Class[]
	 */
	public $classes;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 */
	public function __construct($class_name = null)
	{
		if (isset($class_name)) {
			$this->class_name = $class_name;
		}
		if (!isset($this->classes)) {
			$this->classes = [];
		}
	}

	//--------------------------------------------------------------------------------------- cleanup
	/**
	 * This cleanup method is called after loading and getting the current value
	 * in order to avoid crashes when some components of the setting disappeared in the meantime.
	 *
	 * @return integer number of changes made during cleanup : if 0, then cleanup was not necessary
	 */
	public function cleanup()
	{
		$changes_count = 0;
		$class_name = $this->getClassName();
		foreach ($this->classes as $key => $class) {
			if (
				$class->property_path
				&& !Reflection_Property::exists($class_name, join(DOT, $class->property_path))
			) {
				unset($this->classes[$key]);
				$changes_count ++;
			}
			else {
				$changes_count += $class->cleanup();
			}
		}
		return $changes_count;
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * Get current session / user custom settings object
	 *
	 * @param $class_name string
	 * @return Import_Settings
	 */
	public static function current($class_name)
	{
		return parent::current($class_name);
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function getClassName()
	{
		if (empty($this->class_name)) {
			foreach ($this->classes as $class_key => $class) {
				if (!$class_key) {
					return ($this->class_name = $class->class_name);
				}
			}
		}
		return $this->class_name;
	}

	//------------------------------------------------------------------------------------ getSummary
	/**
	 * @return string
	 */
	public function getSummary()
	{

	}

	//------------------------------------------------------------------------------------------ load
	/**
	 * Loads Import_Settings from the Settings set
	 *
	 * If no Import_Settings named $name is stored, a new one will be returned
	 *
	 * @param $class_name string
	 * @param $name       string
	 * @return Import_Settings
	 */
	public static function load($class_name, $name)
	{
		return parent::load($class_name, $name);
	}

	//---------------------------------------------------------------------------------- setConstants
	/**
	 * @param $constants string[] key is the property path (can be translated or alias)
	 */
	public function setConstants($constants)
	{
		$class_name = $this->getClassName();
		$properties_alias = Import_Array::getPropertiesAlias($class_name);
		$use_reverse_translation = Locale::current() ? true : false;
		foreach ($constants as $property_path => $value) {
			$property_path = Import_Array::propertyPathOf(
				$class_name, $property_path, $use_reverse_translation, $properties_alias
			);
			$property_name = (($i = strrpos($property_path, DOT)) === false)
				? $property_path : substr($property_path, $i + 1);
			$master_path = substr($property_path, 0, $i);
			if (isset($this->classes[$master_path])) {
				$this->classes[$master_path]->constants[$property_name] = new Reflection_Property_Value(
					$this->classes[$master_path]->class_name, $property_name, $value, true
				);
			}
		}
	}

}
