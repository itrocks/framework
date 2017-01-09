<?php
namespace ITRocks\Framework\Reflection\Interfaces;

use ITRocks\Framework\Reflection\Type;

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

	//----------------------------------------------------------------------------- getDeclaringTrait
	/**
	 * Gets the declaring trait for the reflected property
	 * If the property has been declared into a class, this returns this class
	 *
	 * @return Reflection_Class
	 */
	public function getDeclaringTrait();

	//------------------------------------------------------------------------- getDeclaringTraitName
	/**
	 * Gets the declaring trait name for the reflected property
	 * If the property has been declared into a class, this returns this class name
	 *
	 * @returns string
	 */
	public function getDeclaringTraitName();

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

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Gets value
	 *
	 * @param object $object
	 * @return mixed
	 */
	public function getValue($object);

	//-------------------------------------------------------------------------------------- isStatic
	/**
	 * Checks if property is static
	 *
	 * @return boolean
	 */
	public function isStatic();

}
