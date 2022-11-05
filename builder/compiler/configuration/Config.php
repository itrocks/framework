<?php
namespace ITRocks\Framework\Builder\Compiler\Configuration;

use ITRocks\Framework\Builder\Compiler\Configuration;
use ITRocks\Framework\PHP\Reflection_Source;

/**
 * config.php configuration file compiler (plugins)
 */
class Config extends Configuration
{

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $source Reflection_Source
	 */
	public function compile(Reflection_Source $source) : void
	{
		// TODO NORMAL activate / deactivate plugins immediately when the configuration changes
	}

}
