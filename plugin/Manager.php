<?php
namespace ITRocks\Framework\Plugin;

use ITRocks\Framework\AOP\Weaver;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Plugin;
use ITRocks\Framework\Reflection\Reflection_Class;
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
	private $activated = [];

	//-------------------------------------------------------------------------------------- $plugins
	/**
	 * The plugins list : key is the class name
	 *
	 * @var array
	 */
	private $plugins = [];

	//--------------------------------------------------------------------------------- $plugins_tree
	/**
	 * The plugins tree (per level)
	 *
	 * @var array
	 */
	private $plugins_tree = [];

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
		/** @var $plugin Plugin|Activable */
		$plugin = $this->get($class_name);
		return $plugin;
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

	//------------------------------------------------------------------------------------ addPlugins
	/**
	 * Add already registered and activated plugins object to a given level
	 *
	 * @param $level   string
	 * @param $plugins Plugin[]
	 */
	public function addPlugins($level, array $plugins)
	{
		if (!isset($this->plugins_tree[$level])) {
			$this->plugins_tree[$level] = [];
		}
		$this->plugins_tree[$level] = array_merge($this->plugins_tree[$level], $plugins);
		$this->plugins              = array_merge($plugins, $this->plugins);
		foreach (array_keys($plugins) as $class_name) {
			$this->activated[$class_name] = true;
		}
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Gets a plugin object
	 * If no plugin of this class name exists, the class is instantiated and the plugin registered
	 *
	 * @noinspection PhpDocMissingThrowsInspection $class_name must be a valid class name
	 * @param $class_name string
	 * @param $level      string
	 * @param $register   boolean
	 * @param $activate   boolean
	 * @return Plugin
	 */
	public function get($class_name, $level = null, $register = false, $activate = false)
	{
		/** @var $plugin Plugin|boolean|string */
		if (isset($this->plugins[$class_name])) {
			$plugin = $this->plugins[$class_name];
		}
		/** @noinspection PhpUnhandledExceptionInspection */
		elseif (
			(
				!($constructor = ($class = new Reflection_Class($class_name))->getConstructor())
				|| !$constructor->getNumberOfRequiredParameters()
			)
			&& !$class->isAbstract()
		) {
			if (!$level) {
				$level = Priority::NORMAL;
			}
			/** @noinspection PhpUnhandledExceptionInspection */
			$plugin   = Builder::create($class_name);
			$register = true;
			$this->plugins[$class_name]              = $plugin;
			$this->plugins_tree[$level][$class_name] = $plugin;
		}
		else {
			return null;
		}
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
				/** @noinspection PhpUnhandledExceptionInspection must be valid */
				$plugin = Builder::create($class_name, [$serialized]);
				$plugin->plugin_configuration = $serialized;
			}
			// serialized object or configuration or configuration constant
			elseif (is_string($serialized) || is_numeric($serialized)) {
				if ((is_a($class_name, Serializable::class, true))) {
					$plugin = unserialize($serialized);
				}
				else {
					if (str_contains($serialized, ':')) {
						$configuration = @unserialize($serialized);
						if ($configuration === false) {
							$configuration = $serialized;
						}
					}
					else {
						$configuration = $serialized;
					}
					/** @noinspection PhpUnhandledExceptionInspection */
					$plugin = Builder::create($class_name, [$configuration]);
					$plugin->plugin_configuration = $configuration;
				}
			}
			else {
				/** @noinspection PhpUnhandledExceptionInspection */
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
			$protect  = null;
			$activate = true;
		}
		// register plugin
		if ($register && ($plugin instanceof Registerable)) {
			$weaver = isset($this->plugins[Weaver::class])
				? $this->plugins[Weaver::class]
				: null;
			$plugin->register(new Register(
					isset($plugin->plugin_configuration) ? $plugin->plugin_configuration : null, $weaver)
			);
			$activate = true;
		}
		// activate plugin
		if ($activate && ($plugin instanceof Activable)) {
			if (isset($this->activated[$class_name])) {
				trigger_error(
					'Plugin ' . $class_name . ' just registered and already activated', E_USER_WARNING
				);
			}
			else {
				$plugin->activate();
				$this->activated[$class_name] = $plugin;
			}
		}
		return $plugin;
	}

	//---------------------------------------------------------------------------------------- getAll
	/**
	 * Get all plugins list
	 * $tree == false : the key is the plugin class name, the value is a Plugin or string or string[]
	 * $tree == true : first dimension key is priority level, then come the plugins list
	 *
	 * @param $tree boolean If true, return plugins list as a tree where first key is priority level
	 * @return array plugins list
	 */
	public function getAll($tree = false)
	{
		return $tree ? $this->plugins_tree : $this->plugins;
	}

	//------------------------------------------------------------------------------ getConfiguration
	/**
	 * @param $class_name string the plugin class name
	 * @return array the plugin configuration, if set
	 */
	public function getConfiguration($class_name)
	{
		$plugin = $this->get($class_name);
		return isset($plugin->plugin_configuration)
			? $plugin->plugin_configuration
			: null;
	}

	//------------------------------------------------------------------------------------------- has
	/**
	 * Returns true if the manager has the given plugin
	 *
	 * @param $class_name string
	 * @return boolean
	 */
	public function has($class_name)
	{
		return isset($this->plugins[$class_name]);
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
			$this->plugins[$class_name]              = $configuration;
		}
		return $this->get($class_name, $level, $register, $register);
	}

	//------------------------------------------------------------------------------------- serialize
	/**
	 * @return string
	 */
	public function serialize()
	{
		$data = [];
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

	//------------------------------------------------------------------------------------------- set
	/**
	 * Sets a plugin instance
	 *
	 * You can use it to replace a plugin instance with another one
	 * If you replace an existing plugin by a plugin that has a child class, you must tell the base
	 * name of the class with $plugin_class
	 *
	 * @param $plugin     object the instance of the plugin to set (or to remove if null)
	 * @param $class_name string default is the class of $plugin
	 * @return object the replaced plugin if there was one for the given class name
	 */
	public function set($plugin, $class_name = null)
	{
		if (!$class_name) {
			$class_name = get_class($plugin);
		}
		$old_plugin = $this->get($class_name);
		if ($plugin) {
			$this->plugins[$class_name] = $plugin;
		}
		else {
			unset($this->plugins[$class_name]);
		}
		return $old_plugin;
	}

	//----------------------------------------------------------------------------------- unserialize
	/**
	 * @param $serialized string
	 */
	public function unserialize($serialized)
	{
		$this->plugins      = [];
		$this->plugins_tree = unserialize($serialized);
		foreach ($this->plugins_tree as $plugins) {
			$this->plugins = array_merge($this->plugins, $plugins);
		}
	}

}
