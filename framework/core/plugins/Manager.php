<?php
namespace SAF\Plugins;

use SAF\AOP;
use SAF\Framework\Builder;
use Serializable;

/**
 * Plugins manager
 */
class Manager implements IManager, Serializable
{

	//------------------------------------------------------------------------------------ $activated
	/**
	 * @var boolean[] key is the plugin class name, value is always true when set here
	 */
	private $activated = array();

	//--------------------------------------------------------------------------------- $plugins_tree
	/**
	 * The plugins tree (per level)
	 *
	 * @var array
	 */
	private $plugins_tree = array();

	//------------------------------------------------------------------------------------------- $plugins
	/**
	 * The plugins list : key is the class name
	 *
	 * @var array
	 */
	private $plugins = array();

	//-------------------------------------------------------------------------------------- activate
	/**
	 * Activates a plugin : at session creation, at session resuming for already loaded classes and
	 * core plugins, when a plugin class is included
	 *
	 * @param $class_name string
	 * @return Activable
	 */
	public function activate($class_name)
	{
		return $this->get($class_name);
	}

	//------------------------------------------------------------------------------- activatePlugins
	/**
	 * @param $level string
	 */
	public function activatePlugins($level = null)
	{
		foreach ($this->plugins_tree as $tree_level => $plugins) {
			foreach (array_keys($plugins) as $class_name) {
				if (
					class_exists($class_name, false) || trait_exists($class_name, false)
					|| ($tree_level === $level)
				) {
					$this->get($class_name, $level);
				}
			}
		}
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Gets a plugin object
	 *
	 * @param $class_name string
	 * @param $level      string
	 * @param $register   boolean
	 * @param $activate   boolean
	 * @return Plugin
	 */
	public function get($class_name, $level = null, $register = false, $activate = false)
	{
		/** @var $plugin Plugin|boolean|string */
		$plugin = isset($this->plugins[$class_name])
			? $this->plugins[$class_name]
			: Builder::create($class_name);
		// unserialize plugin
		if (!is_object($plugin)) {
			static $protect = null;
			if ($class_name == $protect) {
				return null;
			}
			$protect = $class_name;
			if (!isset($plugin)) {
				trigger_error('Get plugin ' . $class_name . ' : it does not exist', E_USER_ERROR);
			}
			$serialized = $plugin;
			// configuration
			if (is_array($serialized)) {
				$plugin = Builder::create($class_name, array($serialized));
				/** @noinspection PhpUndefinedFieldInspection */
				$plugin->plugin_configuration = $serialized;
			}
			// serialized object or configuration
			elseif (is_string($serialized)) {
				if ((is_a($class_name, Serializable::class, true))) {
					$plugin = unserialize($serialized);
				}
				else {
					$configuration = unserialize($serialized);
					$plugin = Builder::create($class_name, array($configuration));
					/** @noinspection PhpUndefinedFieldInspection */
					$plugin->plugin_configuration = $configuration;
				}
			}
			else {
				$plugin = Builder::create($class_name);
			}
			// store plugin object into manager
			$this->plugins[$class_name] = $plugin;
			if (isset($level)) {
				$this->plugins_tree[$level][$class_name] = $plugin;
			}
			else {
				foreach ($this->plugins_tree as $level => $plugins) {
					if (isset($plugins[$class_name])) {
						$this->plugins_tree[$level][$class_name] = $plugin;
						break;
					}
				}
			}
			$protect = null;
			$activate = true;
		}
		// register plugin
		if ($register && ($plugin instanceof Registerable)) {
			$weaver = isset($this->plugins[AOP\Weaver::class])
				? $this->plugins[AOP\Weaver::class]
				: null;
			/** @var $plugin Registerable */
			$plugin->register(new Register(
					isset($plugin->plugin_configuration) ? $plugin->plugin_configuration : null, $weaver)
			);
			$activate = true;
		}
		// activate plugin
		if ($activate && ($plugin instanceof Activable)) {
			if (isset($this->activated[$class_name])) {
				trigger_error(
					'Plugin ' . $class_name . ' just registered and already activated', E_USER_ERROR
				);
			}
			else {
				/** @var $plugin Activable */
				$plugin->activate();
				$this->activated[$class_name] = $plugin;
			}
		}
		return $plugin;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers a plugin : at session creation, or when a plugin is added
	 *
	 * @param $class_name    string
	 * @param $level         string
	 * @param $configuration array|boolean
	 * @param $register      boolean
	 * @return Plugin
	 */
	public function register($class_name, $level, $configuration = true, $register = true)
	{
		if (!isset($this->plugins[$class_name])) {
			if (empty($configuration)) {
				$configuration = true;
			}
			$this->plugins_tree[$level][$class_name] = $configuration;
			$this->plugins[$class_name] = $configuration;
		}
		return $this->get($class_name, $level, $register, $register);
	}

	//------------------------------------------------------------------------------------- serialize
	/**
	 * @return string
	 */
	public function serialize()
	{
		$data = array();
		foreach ($this->plugins_tree as $level => $plugins) {
			if ($level != 'top_core') {
				foreach ($plugins as $class_name => $object) {
					if (is_object($object)) {
						if ($object instanceof Serializable) {
							$data[$level][$class_name] = serialize($object);
						}
						elseif (isset($object->plugin_configuration)) {
							$data[$level][$class_name] = serialize($object->plugin_configuration);
						}
						else {
							$data[$level][$class_name] = true;
						}
					}
					else {
						$data[$level][$class_name] = $object;
					}
				}
			}
		}
		return serialize($data);
	}

	//------------------------------------------------------------------------------------ addPlugins
	/**
	 * Add already registered and activated plugins object to a given level
	 *
	 * @param $level   string
	 * @param $plugins Plugin[]
	 */
	public function addPlugins($level, $plugins)
	{
		if (!isset($this->plugins_tree[$level])) {
			$this->plugins_tree[$level] = array();
		}
		$this->plugins_tree[$level] = array_merge($this->plugins_tree[$level], $plugins);
		$this->plugins = array_merge($plugins, $this->plugins);
		foreach (array_keys($plugins) as $class_name) {
			$this->activated[$class_name] = true;
		}
	}

	//----------------------------------------------------------------------------------- unserialize
	/**
	 * @param $serialized string
	 */
	public function unserialize($serialized)
	{
		$this->plugins_tree = unserialize($serialized);
		$this->plugins = array();
		foreach ($this->plugins_tree as $plugins) {
			$this->plugins = array_merge($this->plugins, $plugins);
		}
	}

}
