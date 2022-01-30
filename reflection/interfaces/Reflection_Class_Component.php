<?php
namespace ITRocks\Framework\Reflection\Interfaces;

use ReflectionClass;

/**
 * An interface for all reflection class component
 */
interface Reflection_Class_Component extends Reflection
{

	//----------------------------------------------------------------------------- getDeclaringClass
	/**
	 * Gets the declaring class for the reflected property.
	 * If the property has been declared into a trait, returns the class that uses this trait.
	 *
	 * @return Reflection_Class|ReflectionClass
	 */
	public function getDeclaringClass() : Reflection_Class|ReflectionClass;

	//------------------------------------------------------------------------- getDeclaringClassName
	/**
	 * Gets the declaring class name for the reflected property.
	 * If the property has been declared into a trait, returns the name of the class using the trait.
	 *
	 * @return string
	 */
	public function getDeclaringClassName() : string;

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * Gets doc comment
	 *
	 * @return string
	 */
	public function getDocComment() : string;

}
