<?php
namespace SAF\Framework\Reflection\Interfaces;

/**
 * An interface for all reflection class component
 */
interface Reflection_Class_Component extends Reflection
{

	//----------------------------------------------------------------------------- getDeclaringClass
	/**
	 * Gets declaring class
	 *
	 * @return Reflection_Class
	 */
	public function getDeclaringClass();

	//------------------------------------------------------------------------- getDeclaringClassName
	/**
	 * Gets declaring class name
	 *
	 * @return string
	 */
	public function getDeclaringClassName();

}
