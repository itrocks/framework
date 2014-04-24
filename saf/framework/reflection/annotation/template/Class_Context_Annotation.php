<?php
namespace SAF\Framework\Reflection\Annotation\Template;

use SAF\Framework\PHP;
use SAF\Framework\Reflection\Interfaces\Reflection_Class;

/**
 * A class context annotation needs the class to be properly built
 */
interface Class_Context_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 * @param $class Reflection_Class The contextual Reflection_Class object
	 */
	public function __construct($value, Reflection_Class $class);

}
