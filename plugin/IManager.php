<?php
namespace ITRocks\Framework\Plugin;

use ITRocks\Framework\Plugin;

/**
 * The plugins manager standard interface
 */
interface IManager
{

	//-------------------------------------------------------------------------------------- activate
	/**
	 * Activates a plugin : at session creation, at session resuming for already loaded classes and
	 * core plugins, when a plugin class is included
	 *
	 * @param $class_name string
	 * @return Activable
	 */
	public function activate($class_name);

	//------------------------------------------------------------------------------- activatePlugins
	/**
	 * @param $level string
	 */
	public function activatePlugins($level = null);

	//------------------------------------------------------------------------------------ addPlugins
	/**
	 * Add already registered and activated plugins object to a given level
	 *
	 * @param $level   string
	 * @param $plugins Plugin[]
	 */
	public function addPlugins($level, array $plugins);

	//------------------------------------------------------------------------------------------- get
	/**
	 * Gets a plugin object
	 *
	 * @param $class_name class-string<T>
	 * @param $level      string|null
	 * @param $register   boolean
	 * @return T|null
	 * @template T
	 */
	public function get(string $class_name, string $level = null, bool $register = false)
		: object|null;

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers a plugin : called at session creation, or when a new plugin is loaded
	 *
	 * @param $class_name    string
	 * @param $level         string
	 * @param $configuration array|boolean
	 * @return Plugin
	 */
	public function register($class_name, $level, $configuration = true);

}
