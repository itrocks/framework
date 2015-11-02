<?php
namespace SAF\Framework\Reflection\Interfaces;

/**
 * An interface for all reflection method classes
 */
interface Reflection_Method extends Reflection_Class_Component
{

	/**
	 * Another constant for default Reflection_Class::getMethods() filter
	 *
	 * @var integer
	 */
	const ALL = 1799;

	//----------------------------------------------------------------------------- getDeclaringClass
	/**
	 * @return Reflection_Class
	 */
	public function getDeclaringClass();

	//---------------------------------------------------------------------------- getParametersNames
	/**
	 * @return string[] key and value are both the parameter name
	 */
	public function getParametersNames();

	//---------------------------------------------------------------------------- getPrototypeString
	/**
	 * The prototype of the function, beginning with first whitespaces before function and its doc
	 * comments, ending with { or ; followed by LF.
	 *
	 * @return string
	 */
	public function getPrototypeString();

	//------------------------------------------------------------------------------------ isAbstract
	/**
	 * @return boolean
	 */
	public function isAbstract();

	//--------------------------------------------------------------------------------- isConstructor
	/**
	 * @return boolean
	 */
	public function isConstructor();

	//---------------------------------------------------------------------------------- isDestructor
	/**
	 * @return boolean
	 */
	public function isDestructor();

	//--------------------------------------------------------------------------------------- isFinal
	/**
	 * @return boolean
	 */
	public function isFinal();

	//------------------------------------------------------------------------------------ isInternal
	/**
	 * @return boolean
	 */
	public function isInternal();

	//------------------------------------------------------------------------------------- isPrivate
	/**
	 * @return boolean
	 */
	public function isPrivate();

	//----------------------------------------------------------------------------------- isProtected
	/**
	 * @return boolean
	 */
	public function isProtected();

	//-------------------------------------------------------------------------------------- isPublic
	/**
	 * @return boolean
	 */
	public function isPublic();

	//-------------------------------------------------------------------------------------- isStatic
	/**
	 * @return boolean
	 */
	public function isStatic();

	//--------------------------------------------------------------------------------- isUserDefined
	/**
	 * @return boolean
	 */
	public function isUserDefined();

	//--------------------------------------------------------------------------------------- returns
	/**
	 * @return string
	 */
	public function returns();

	//------------------------------------------------------------------------------ returnsReference
	/**
	 * @return boolean
	 */
	public function returnsReference();

}
