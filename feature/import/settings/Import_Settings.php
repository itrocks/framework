<?php
namespace ITRocks\Framework\Feature\Import\Settings;

use ITRocks\Framework\Feature\Import\Import_Array;
use ITRocks\Framework\Locale;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Setting;

/**
 * Import settings
 */
class Import_Settings extends Setting\Custom\Set
{

	//-------------------------------------------------------------------------------------- $classes
	/**
	 * @var Import_Class[]
	 */
	public array $classes;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string|null
	 */
	public function __construct($class_name = null)
	{
		parent::__construct($class_name);
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
	public function cleanup() : int
	{
		$changes_count = 0;
		$class_name    = $this->getClassName();
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
	 * @param $feature    string
	 * @return static
	 */
	public static function current(string $class_name, string $feature = '') : static
	{
		if (!isset($feature)) {
			$feature = 'import';
		}
		return parent::current($class_name, $feature);
	}

	//---------------------------------------------------------------------------------- getClassName
	/**
	 * @return string
	 */
	public function getClassName() : string
	{
		if (!parent::getClassName()) {
			foreach ($this->classes as $class_key => $class) {
				if (!$class_key) {
					$this->setClassName($class->class_name);
					break;
				}
			}
		}
		return parent::getClassName();
	}

	//---------------------------------------------------------------------------------- setConstants
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $constants string[] key is the property path (can be translated or alias)
	 */
	public function setConstants(array $constants)
	{
		$class_name              = $this->getClassName();
		$properties_alias        = Import_Array::getPropertiesAlias($class_name);
		$use_reverse_translation = (bool)Locale::current();
		foreach ($constants as $property_path => $value) {
			$property_path = Import_Array::propertyPathOf(
				$class_name, $property_path, $use_reverse_translation, $properties_alias
			);
			$property_name = (($i = strrpos($property_path, DOT)) === false)
				? $property_path
				: substr($property_path, $i + 1);
			$master_path = substr($property_path, 0, $i);
			if (isset($this->classes[$master_path])) {
				/** @noinspection PhpUnhandledExceptionInspection class name and property are valid */
				$this->classes[$master_path]->constants[$property_name] = new Reflection_Property_Value(
					$this->classes[$master_path]->class_name, $property_name, $value, true
				);
			}
		}
	}

}
