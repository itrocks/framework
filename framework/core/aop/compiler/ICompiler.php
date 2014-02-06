<?php
namespace SAF\AOP;

/**
 * Aspect compiler
 */
interface ICompiler
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param IWeaver $weaver
	 */
	public function __construct(IWeaver $weaver);

	//--------------------------------------------------------------------------------------- compile
	/**
	 */
	public function compile();

}
