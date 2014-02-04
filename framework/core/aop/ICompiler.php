<?php
namespace SAF\AOP;

/**
 * Aspect compiler
 */
interface ICompiler
{

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $weaver IWeaver
	 */
	public function compile(IWeaver $weaver);

}
