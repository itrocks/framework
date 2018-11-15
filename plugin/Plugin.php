<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Plugin\Priority;

/**
 * The Plugin interface must be used to define plugins
 *
 * Plugins can be Registerable, Activable and Plugins\Configurable too
 * Their priority can be defined as default, and can be overridden when installed as a dependency
 */
interface Plugin
{

	//-------------------------------------------------------------------------------------- PRIORITY
	const PRIORITY = Priority::NORMAL;

}
