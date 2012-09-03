<?php

class Configurations
{

	/**
	 * @var Configuration[]
	 */
	private $configurations;

	//-------------------------------------------------------------------------- getAllConfigurations
	public function getAllConfigurations()
	{
		return $this->configurations;
	}

	//------------------------------------------------------------------------------ getConfiguration
	public function getConfiguration($configuration_name)
	{
		return $this->configurations[$configuration_name];
	}

	//------------------------------------------------------------------------------------------ load
	/**
	 * @param string $file_name
	 */
	public function load($file_name = "config.php")
	{
		$config = array();
		include $file_name;
		$this->configurations = array();
		foreach ($config as $config_name => $config_options) {
			$this->configurations[$config_name] = new Configuration($config_options);
		}
	}

}
