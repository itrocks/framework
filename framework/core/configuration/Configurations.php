<?php
namespace SAF\Framework;

require_once "framework/core/configuration/Configurations.php";

class Configurations
{

	//------------------------------------------------------------------------------- $configurations
	/**
	 * Configuration objects array, indice is configuration name
	 *
	 * @var multitype:Configuration
	 */
	private $configurations;

	//-------------------------------------------------------------------------- getAllConfigurations
	/*
	 * Get all the loaded configurations
	 *
	 * @return multitype:Configuration
	 */
	public function getAllConfigurations()
	{
		return $this->configurations;
	}

	//------------------------------------------------------------------------------ getConfiguration
	/**
	 * Get the named $configuration_name Configuration object
	 *
	 * @param string $configuration_name
	 * @return Configuration
	 */
	public function getConfiguration($configuration_name)
	{
		return $this->configurations[$configuration_name];
	}

	//------------------------------------------------------------------------------------------ load
	/**
	 * Load the config.php configuration file an store it into the configurations list
	 *
	 * If a default configuration is set into the loaded configuration file, current configuration is switched to this configuration.
	 *
	 * @param string $file_name
	 */
	public function load($file_name = "config.php")
	{
		$config = array();
		include $file_name;
		if (isset($GLOBALS["CONFIG"])) {
			$config = arrayMergeRecursive($config, array_reverse($GLOBALS["CONFIG"], true));
		}
		$this->configurations = array();
		foreach ($config as $config_name => $config_options) {
			if (isset($config_options["extends"])) {
				$config_options = arrayMergeRecursive(
					$this->getConfiguration($config_options["extends"])->toArray(),
					$config_options
				);
			}
			$this->configurations[$config_name] = new Configuration($config_options);
		}
		if (!Configuration::current()) {
			Configuration::current(end($this->configurations));
		}
	}

}
