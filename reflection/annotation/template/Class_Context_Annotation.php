<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;

/**
 * A class context annotation needs the class to be properly built
 * Annotations class that are intended for class only should implement this
 *
 * @see Property_Context_Annotation
 * @see Reflection_Context_Annotation
 */
interface Class_Context_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value ?string
	 * @param $class Reflection_Class The contextual Reflection_Class object
	 */
	public function __construct(?string $value, Reflection_Class $class);

}
