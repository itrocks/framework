<?php
namespace ITRocks\Framework\Plugin;

use ITRocks\Framework\Plugin;

/**
 * Registerable plugins will be registered the first time they are used : at session start, or at
 * will in case of dynamic plugin loading
 */
interface Registerable extends Plugin
{

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register) : void;

}
