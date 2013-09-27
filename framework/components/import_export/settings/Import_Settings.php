<?php
namespace SAF\Framework;

/**
 * Import settings
 */
class Import_Settings
{
	use Custom_Settings { current as private pCurrent; load as private pLoad; }

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
			$this->classes = array();
		}
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * Get current session / user custom settings object
	 *
	 * @param $class_name string
	 * @return List_Settings
	 */
	public static function current($class_name)
	{
		return self::pCurrent($class_name);
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

	//------------------------------------------------------------------------------------------ load
	/**
	 * Loads a List_Settings from the Settings set
	 *
	 * If no List_Settings named $name is stored, a new one will be returned
	 *
	 * @param $class_name string
	 * @param $name       string
	 * @return List_Settings
	 */
	public static function load($class_name, $name)
	{
		return self::pLoad($class_name, $name);
	}

}
