<?php
namespace ITRocks\Framework\Configuration\File\Config;

use ITRocks\Framework;
use ITRocks\Framework\Configuration\File\Config;
use ITRocks\Framework\Reflection\Attribute\Property\Values;

/**
 * Priority block
 *
 * Contains plugin configurations
 */
class Priority
{

	//--------------------------------------------------------------------------------------- $config
	public Config $config;

	//-------------------------------------------------------------------------------------- $plugins
	/** @var Plugin[]|string[] plugin configuration or free plugin configuration code */
	public array $plugins = [];

	//------------------------------------------------------------------------------------- $priority
	#[Values(Framework\Plugin\Priority::class)]
	public string $priority;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Priority constructor
	 *
	 * @param $priority string @values Framework\Plugin\Priority::const
	 * @see Framework\Plugin\Priority::const
	 */
	public function __construct(string $priority)
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
	public function addPlugin(string $plugin_name, mixed $configuration) : Plugin
	{
		$plugin = $this->searchPlugin($plugin_name);
		if (!$plugin) {
			$builder            = $this->config;
			$plugin             = new Plugin();
			$plugin->class_name = $plugin_name;
			$this->plugins      = objectInsertSorted(
				$this->plugins,
				$plugin,
				function(Plugin $plugin1, Plugin $plugin2) use($builder) : int {
					$class1 = $builder->shortClassNameOf($plugin1->class_name, 2);
					$class2 = $builder->shortClassNameOf($plugin2->class_name, 2);
					return strcmp($class1, $class2);
				}
			);
		}
		$plugin->configuration = $configuration;
		return $plugin;
	}

	//------------------------------------------------------------------------ emptyIfNoPluginAnymore
	/**
	 * Empty the plugin list if the priority does not contain plugins anymore
	 *
	 * @return boolean true if has been emptied, false if the priority still contains plugins
	 */
	public function emptyIfNoPluginAnymore() : bool
	{
		foreach ($this->plugins as $plugin) {
			if ($plugin instanceof Plugin) {
				return false;
			}
		}
		$this->plugins = [];
		return true;
	}

	//---------------------------------------------------------------------------------- removePlugin
	/**
	 * @param $plugin_name string
	 * @return integer
	 */
	public function removePlugin(string $plugin_name) : int
	{
		$removed = 0;
		foreach ($this->plugins as $plugin_key => $plugin) {
			if (($plugin instanceof Plugin) && ($plugin->class_name === $plugin_name)) {
				unset($this->plugins[$plugin_key]);
				$removed ++;
			}
		}
		return $removed;
	}

	//---------------------------------------------------------------------------------- searchPlugin
	/**
	 * Search a plugin
	 *
	 * @param $plugin_name string plugin class name
	 * @return ?Plugin
	 */
	public function searchPlugin(string $plugin_name) : ?Plugin
	{
		foreach ($this->plugins as $plugin) {
			if (($plugin instanceof Plugin) && ($plugin->class_name === $plugin_name)) {
				return $plugin;
			}
		}
		return null;
	}

}
