<?php
namespace SAF\Framework\Reflection\Interfaces;

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
	 * @return Reflection_Class
	 */
	public function getDeclaringClass();

	//------------------------------------------------------------------------- getDeclaringClassName
	/**
	 * Gets the declaring class name for the reflected property.
	 * If the property has been declared into a trait, returns the name of the class using the trait.
	 *
	 * @return string
	 */
	public function getDeclaringClassName();

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * Gets doc comment
	 *
	 * @return string
	 */
	public function getDocComment();

}
