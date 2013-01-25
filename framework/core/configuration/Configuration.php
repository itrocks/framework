<?php
namespace SAF\Framework;

require_once "framework/core/toolbox/Current.php";

class Configuration
{
	use Current { current as private pCurrent; }

	//------------------------------------------------------------------------------------------ $app
	/**
	 * @var string
	 */
	private $app;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Build configuration using configurations options
	 *
	 * Default configuration is set to the configuration if the "default" option is set to true.
	 *
	 * @param $configuration_options array recursive configuration array from the config.php file
	 */
	public function __construct($configuration_options)
	{
		foreach ($configuration_options as $name => $value) {
			$this->$name = $value;
		}
		if (isset($configuration_options["default"]) && $configuration_options["default"]) {
			self::current($this);
		}
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * Sets / gets current configuration
	 *
	 * When current configuration is set, each "class" property is changed into it's current object initialised with configuration parameters
	 *
	 * @param $set_current Configuration
	 * @return Configuration
	 */
	public static function current(Configuration $set_current = null)
	{
		if (isset($set_current)) {
			$set_current = self::pCurrent($set_current);
			foreach (
				$set_current->getClassesConfigurations() as $class_name => $configuration
			) {
				$full_class_name = Namespaces::fullClassName($class_name);
				$configuration_class_name = isset($configuration["class"])
					? Namespaces::fullClassName($configuration["class"])
					: $full_class_name;
				$builder_class_name = Namespaces::fullClassName(
					rLastParse($configuration_class_name, "\\") . "_Builder_Configuration"
				);
				if (class_exists($builder_class_name)) {
					$full_class_name::current((new $builder_class_name())->build($configuration));
				}
				else {
					$full_class_name::current(new $configuration_class_name($configuration));
				}
			}
			return $set_current;
		}
		else {
			return self::pCurrent($set_current);
		}
	}

	//---------------------------------------------------------------------------- getApplicationName
	/**
	 * Get the configuration's application name
	 *
	 * @return string
	 */
	public function getApplicationName()
	{
		return $this->app;
	}

	//---------------------------------------------------------------------- getClassesConfigurations
	/**
	 * Returns full configuration array for each class configuration
	 *
	 * @return array[]
	 */
	public function getClassesConfigurations()
	{
		$classes = array();
		foreach (get_object_vars($this) as $name => $value) {
			if (($name[0] >= "A") && ($name[0] <= "Z")) {
				$classes[$name] = $value;
			}
		}
		return $classes;
	}

	//--------------------------------------------------------------------------------------- toArray
	/**
	 * Returns a configuration as an associative array like in config.php file
	 *
	 * @return array
	 */
	public function toArray()
	{
		return get_object_vars($this);
	}

}
