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
	public function activate(string $class_name) : Activable;

	//------------------------------------------------------------------------------- activatePlugins
	/**
	 * @param $level string|null
	 */
	public function activatePlugins(string $level = null);

	//------------------------------------------------------------------------------------ addPlugins
	/**
	 * Add already registered and activated plugins object to a given level
	 *
	 * @param $level   string
	 * @param $plugins Plugin[]
	 */
	public function addPlugins(string $level, array $plugins);

	//------------------------------------------------------------------------------------------- get
	/**
	 * Gets a plugin object
	 *
	 * @param $class_name class-string<T>
	 * @param $level      string|null
	 * @param $register   boolean
	 * @return ?T
	 * @template T
	 */
	public function get(string $class_name, string $level = null, bool $register = false) : ?object;

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers a plugin : called at session creation, or when a new plugin is loaded
	 *
	 * @param $class_name    string
	 * @param $level         string
	 * @param $configuration array|boolean
	 * @return Plugin
	 */
	public function register(string $class_name, string $level, array|bool $configuration = true)
		: Plugin;

}
