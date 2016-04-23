<?php
namespace SAF\Framework\PHP;

/**
 * All Php compiler files should use this interface
 */
interface ICompiler
{

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $source   Reflection_Source the PHP source file object
	 * @param $compiler Compiler the main compiler
	 * @return boolean true if compilation process did something, else false
	 */
	public function compile(Reflection_Source $source, Compiler $compiler = null);

	//-------------------------------------------------------------------------- moreSourcesToCompile
	/**
	 * Extends the list of sources to compile
	 *
	 * @param &$sources Reflection_Source[]
	 * @return Reflection_Source[] added sources list
	 */
	public function moreSourcesToCompile(&$sources);

}
