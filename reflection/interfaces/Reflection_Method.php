<?php
namespace ITRocks\Framework\Reflection\Interfaces;

use ReflectionClass;

/**
 * An interface for all reflection method classes
 */
interface Reflection_Method extends Reflection_Class_Component
{

	//------------------------------------------------------------------------------------------- ALL
	/**
	 * Another constant for default Reflection_Class::getMethods() filter
	 *
	 * @var integer
	 */
	const ALL = 1799;

	//----------------------------------------------------------------------------- getDeclaringClass
	/**
	 * @return Reflection_Class|ReflectionClass
	 */
	public function getDeclaringClass() : Reflection_Class|ReflectionClass;

	//----------------------------------------------------------------------------- getParametersCall
	/**
	 * Return a calling string for parameters call
	 *
	 * @return string ie '$param1, $param2, $param3'
	 */
	public function getParametersCall() : string;

	//---------------------------------------------------------------------------- getParametersNames
	/**
	 * @param $by_name boolean
	 * @return string[] key and value are both the parameter name
	 */
	public function getParametersNames(bool $by_name = true) : array;

	//---------------------------------------------------------------------------- getPrototypeString
	/**
	 * The prototype of the function, beginning with first whitespaces before function and its doc
	 * comments, ending with { or ; followed by LF.
	 *
	 * @return string
	 */
	public function getPrototypeString() : string;

	//------------------------------------------------------------------------------------ isAbstract
	/**
	 * @return boolean
	 */
	public function isAbstract() : bool;

	//--------------------------------------------------------------------------------- isConstructor
	/**
	 * @return boolean
	 */
	public function isConstructor() : bool;

	//---------------------------------------------------------------------------------- isDestructor
	/**
	 * @return boolean
	 */
	public function isDestructor() : bool;

	//--------------------------------------------------------------------------------------- isFinal
	/**
	 * @return boolean
	 */
	public function isFinal() : bool;

	//------------------------------------------------------------------------------------ isInternal
	/**
	 * @return boolean
	 */
	public function isInternal() : bool;

	//------------------------------------------------------------------------------------- isPrivate
	/**
	 * @return boolean
	 */
	public function isPrivate() : bool;

	//----------------------------------------------------------------------------------- isProtected
	/**
	 * @return boolean
	 */
	public function isProtected() : bool;

	//-------------------------------------------------------------------------------------- isPublic
	/**
	 * @return boolean
	 */
	public function isPublic() : bool;

	//-------------------------------------------------------------------------------------- isStatic
	/**
	 * @return boolean
	 */
	public function isStatic() : bool;

	//--------------------------------------------------------------------------------- isUserDefined
	/**
	 * @return boolean
	 */
	public function isUserDefined() : bool;

	//--------------------------------------------------------------------------------------- returns
	/**
	 * @return string
	 */
	public function returns() : string;

	//------------------------------------------------------------------------------ returnsReference
	/**
	 * @return boolean
	 */
	public function returnsReference() : bool;

}
