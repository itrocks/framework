<?php
namespace SAF\Framework\Reflection\Interfaces;

use SAF\Framework\Reflection\Type;

/**
 * An interface for all reflection method classes
 */
interface Reflection_Property extends Reflection_Class_Component
{

	//------------------------------------------------------------------------------------------- ALL
	/**
	 * Another constant for default Reflection_Class::getProperties() filter
	 *
	 * @var integer
	 */
	const ALL = 1793;

	//--------------------------------------------------------------------------------- getFinalClass
	/**
	 * Gets the final class where the property came from with a call to getProperties()
	 *
	 * @return Reflection_Class
	 */
	public function getFinalClass();

	//----------------------------------------------------------------------------- getFinalClassName
	/**
	 * Gets final class name : the one where the property came from with a call to getProperties()
	 *
	 * @return string
	 */
	public function getFinalClassName();

	//--------------------------------------------------------------------------------------- getType
	/**
	 * Gets the type of the property, as defined by its var annotation
	 *
	 * @return Type
	 */
	public function getType();

	//-------------------------------------------------------------------------------------- isStatic
	/**
	 * Checks if property is static
	 *
	 * @return boolean
	 */
	public function isStatic();

}
