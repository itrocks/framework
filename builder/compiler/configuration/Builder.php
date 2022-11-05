<?php
namespace ITRocks\Framework\Builder\Compiler\Configuration;

use ITRocks\Framework\Builder\Compiler\Configuration;
use ITRocks\Framework\PHP\Reflection_Source;

/**
 * builder.php configuration file compiler (built classes)
 */
class Builder extends Configuration
{

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $source Reflection_Source
	 */
	public function compile(Reflection_Source $source) : void
	{
		// adding /removing built classes is already done by Compiler::moreSourcesToCompile
	}

}
