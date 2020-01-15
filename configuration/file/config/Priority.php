<?php
namespace ITRocks\Framework\Configuration\File\Config;

use ITRocks\Framework;
use ITRocks\Framework\Configuration\File\Config;

/**
 * Priority block
 *
 * Contains plugin configurations
 */
class Priority
{

	//--------------------------------------------------------------------------------------- $config
	/**
	 * @var Config
	 */
	public $config;

	//-------------------------------------------------------------------------------------- $plugins
	/**
	 * @var Plugin[]|string[] plugin configuration or free plugin configuration code
	 */
	public $plugins = [];

	//------------------------------------------------------------------------------------- $priority
	/**
	 * The priority constant name
	 *
	 * @see Framework\Plugin\Priority::const
	 * @values Framework\Plugin\Priority::const
	 * @var string
	 */
	public $priority;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Priority constructor
	 *
	 * @param $priority string @values Framework\Plugin\Priority::const
	 * @see Framework\Plugin\Priority::const
	 */
	public function __construct($priority)
	{
		$this->priority = strtolower($priority);
	}

	//------------------------------------------------------------------------------------- addPlugin
	/**
	 * Add a plugin or replace the configuration and return the existing plugin
	 *
	 * @param $plugin_name   string plugin class name
	 * @param $configuration mixed
	 * @return Plugin
	 */
	public function addPlugin($plugin_name, $configuration)
	{
		$plugin = $this->searchPlugin($plugin_name);
		if (!$plugin) {
			$builder            = $this->config;
			$plugin             = new Plugin();
			$plugin->class_name = $plugin_name;
			$this->plugins      = objectInsertSorted(
				$this->plugins,
				$plugin,
				function(Plugin $plugin1, Plugin $plugin2) use($builder) {
					$class1 = $builder->shortClassNameOf($plugin1->class_name, 2);
					$class2 = $builder->shortClassNameOf($plugin2->class_name, 2);
					return strcmp($class1, $class2);
				}
			);
		}
		$plugin->configuration = $configuration;
		return $plugin;
	}

	//---------------------------------------------------------------------------------- removePlugin
	/**
	 * @param $plugin_name string
	 */
	public function removePlugin($plugin_name)
	{
		foreach ($this->plugins as $plugin_key => $plugin) {
			if (($plugin instanceof Plugin) && ($plugin->class_name === $plugin_name)) {
				unset($this->plugins[$plugin_key]);
			}
		}
	}

	//---------------------------------------------------------------------------------- searchPlugin
	/**
	 * Search a plugin
	 *
	 * @param $plugin_name string plugin class name
	 * @return Plugin|null
	 */
	public function searchPlugin($plugin_name)
	{
		foreach ($this->plugins as $plugin) {
			if (($plugin instanceof Plugin) && ($plugin->class_name === $plugin_name)) {
				return $plugin;
			}
		}
		return null;
	}

}
