<?php
namespace ITRocks\Framework\Builder\Compiler;
use ITRocks\Framework\PHP\Reflection_Source;

/**
 * Configuration file compiler commons
 */
abstract class Configuration
{

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $source Reflection_Source
	 */
	abstract public function compile(Reflection_Source $source) : void;

}
