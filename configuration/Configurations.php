<?php
namespace ITRocks\Framework\Configuration;

use ITRocks\Framework\Configuration;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Priority;

/**
 * The available applications configurations management class
 */
class Configurations
{

	//------------------------------------------------------------------------------- $configurations
	/**
	 * Configuration objects array, key is configuration name
	 *
	 * @var Configuration[]
	 */
	private $configurations;

	//-------------------------------------------------------------------------- getAllConfigurations
	/**
	 * Get all the loaded configurations
	 *
	 * @return Configuration[]
	 */
	public function getAllConfigurations()
	{
		return $this->configurations;
	}

	//------------------------------------------------------------------------------ getConfiguration
	/**
	 * Get the named $configuration_name Configuration object
	 *
	 * @param $configuration_name string
	 * @return Configuration
	 */
	public function getConfiguration($configuration_name)
	{
		return $this->configurations[$configuration_name];
	}

	//---------------------------------------------------------- getConfigurationFileNameFromComposer
	/**
	 * Automatically get the current configuration name, if set into your final project's
	 * composer.json file.
	 *
	 * @return string A configuration file name is a string looking like 'vendor/project/config.php'
	 */
	public function getConfigurationFileNameFromComposer()
	{
		if (file_exists('composer.json')) {
			$composer = file_get_contents('composer.json');
			preg_match(
				'~\n\s*\"itrocks-config\":\s*\"(?P<vendor>[\w-]*)/(?P<project>[\w-]*)\"\s*,~',
				$composer,
				$match
			);
			if (!$match) {
				preg_match(
					'~\n\s*\"name\":\s*\"(?P<vendor>[\w-]*)/(?P<project>[\w-]*)\"\s*,~', $composer, $match
				);
			}
			if ($match) {
				if (str_ends_with($match['project'], '-final')) {
					$match['project'] = lLastParse($match['project'], '-final');
				}
				$file_name = $match['vendor'] . SL . $match['project'] . SL . 'config.php';
				$file_name = str_replace('-', '_', $file_name);
				if (!is_file($file_name)) {
					$file_name = 'config.php';
					if (!is_file($file_name)) {
						$file_name = $match['project'] . '.php';
					}
				}
				return is_file($file_name) ? $file_name : null;
			}
		}
		return null;
	}

	//------------------------------------------------------------------------------------------ load
	/**
	 * Load the config.php configuration file an store it into the configurations list
	 *
	 * If a default configuration is set into the loaded configuration file, current configuration is
	 * switched to this configuration.
	 *
	 * @param $file_name string
	 * @return Configuration
	 */
	public function load($file_name = 'config.php')
	{
		$config = [];
		/** @noinspection PhpIncludeInspection */
		include $file_name;
		$configurations = [];
		foreach ($config as $config_name => $config_options) {
			if (isset($config_options['extends'])) {
				$extends_array = is_array($config_options['extends'])
					? $config_options['extends']
					: [$config_options['extends']];
				unset($config_options['extends']);
				foreach ($extends_array as $extends) {
					$config_options = arrayMergeRecursive(
						$configurations[$extends], $config_options, Configurable::CLEAR
					);
				}
			}
			if (!isset($config_options['app'])) {
				$config_options['app'] = $config_name;
			}
			if (!isset($config_options['author'])) {
				$config_options['author'] = 'ITRocks';
			}
			$configurations[$config_name] = $config_options;
		}
		$this->configurations = [];
		foreach ($configurations as $config_name => $config_options) {
			$removed = array_flip($config_options[Priority::REMOVE]);
			unset($config_options[Priority::REMOVE]);
			foreach ($config_options as $level => $plugins) {
				if (is_array($plugins)) {
					$plugins_configurations = [];
					foreach ($plugins as $class_name => $plugin_configuration) {
						if (!isset($removed[$class_name])) {
							if (is_numeric($class_name)) {
								$class_name           = $plugin_configuration;
								$plugin_configuration = [];
							}
							$plugins_configurations[$class_name] = $plugin_configuration;
						}
					}
					$config_options[$level] = $plugins_configurations;
				}
			}
			$this->configurations[$config_name] = new Configuration($config_name, $config_options);
			$this->configurations[$config_name]->file_name = $file_name;
		}
		return end($this->configurations);
	}

}
