<?php
namespace SAF\Framework;

require_once "framework/classes/toolbox/Current.php";

class Configuration
{
	use Current;

	//------------------------------------------------------------------------------------------ $app
	/**
	 * @var array
	 */
	private $app;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Build configuration using configurations options
	 *
	 * Default configuration is set to the configuration if the "default" option is set to true.
	 *
	 * @param array $configuration_options recursive configuration array from the config.php file 
	 */
	public function __construct($configuration_options)
	{
		foreach ($configuration_options as $name => $value) {
			$this->$name = $value;
		}
		if (isset($configuration_options["default"]) && $configuration_options["default"]) {
			Configuration::current($this);
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
	 * @return multitype:array
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
