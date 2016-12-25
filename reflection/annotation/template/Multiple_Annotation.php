<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework\Reflection\Interfaces\Reflection;

/**
 * This is an interface for annotations that accept several cumulative implementations.
 * When read, multiple annotations will be returned as an Annotation[] instead of an Annotation
 */
interface Multiple_Annotation
{

	//-------------------------------------------------------------------------------------------- of
	/**
	 * @param Reflection $reflection_object
	 * @return static[]
	 */
	public static function of(Reflection $reflection_object);

}
