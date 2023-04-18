<?php
namespace ITRocks\Framework\Reflection\Interfaces;

/**
 * An interface for all reflection method classes
 */
interface Reflection_Method extends Reflection_Class_Component
{

	//------------------------------------------------------------------------------------------- ALL
	/** Another constant for default Reflection_Class::getMethods() filter */
	const ALL = 1799;

	//----------------------------------------------------------------------------- getParametersCall
	/**
	 * Return a calling string for parameters call
	 *
	 * @return string e.g. '$param1, $param2, $param3'
	 */
	public function getParametersCall() : string;

	//---------------------------------------------------------------------------- getParametersNames
	/**
	 * @param $by_name boolean
	 * @return string[] key and value are both the parameter name
	 */
	public function getParametersNames(bool $by_name = true) : array;

	//------------------------------------------------------------------------------------- getParent
	public function getParent() : ?Reflection_Method;

	//---------------------------------------------------------------------------- getPrototypeString
	/**
	 * The prototype of the function, beginning with first whitespaces before function and its doc
	 * comments, ending with { or ; followed by LF.
	 */
	public function getPrototypeString() : string;

	//--------------------------------------------------------------------------- getReturnTypeString
	public function getReturnTypeString() : string;

	//------------------------------------------------------------------------------------ isAbstract
	public function isAbstract() : bool;

	//--------------------------------------------------------------------------------- isConstructor
	public function isConstructor() : bool;

	//---------------------------------------------------------------------------------- isDestructor
	public function isDestructor() : bool;

	//--------------------------------------------------------------------------------------- isFinal
	public function isFinal() : bool;

	//------------------------------------------------------------------------------------ isInternal
	public function isInternal() : bool;

	//------------------------------------------------------------------------------------- isPrivate
	public function isPrivate() : bool;

	//----------------------------------------------------------------------------------- isProtected
	public function isProtected() : bool;

	//-------------------------------------------------------------------------------------- isPublic
	public function isPublic() : bool;

	//-------------------------------------------------------------------------------------- isStatic
	public function isStatic() : bool;

	//--------------------------------------------------------------------------------- isUserDefined
	public function isUserDefined() : bool;

	//--------------------------------------------------------------------------------------- returns
	public function returns() : string;

	//------------------------------------------------------------------------------ returnsReference
	public function returnsReference() : bool;

}
