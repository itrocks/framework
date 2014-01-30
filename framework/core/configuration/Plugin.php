<?php
namespace SAF\Framework;

/**
 * The Plugin interface must be used to define plugins
 *
 * Plugins are registered at session start
 * (or at will, but never register them twice into a session unless you unregistered it before)
 */
interface Plugin
{

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Plugin_Register
	 */
	public function register(Plugin_Register $register);

}
