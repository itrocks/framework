<?php
namespace SAF\Framework\Reflection\Annotation\Template;

use SAF\Framework\Reflection\Reflection_Class;

/**
 * A class context annotation needs the class to be properly built
 */
interface Class_Context_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string
	 * @param $property Reflection_Class ie the contextual Reflection_Class object
	 */
	public function __construct($value, Reflection_Class $property);

}
