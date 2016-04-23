<?php
namespace SAF\Framework\Reflection\Annotation\Template;

use SAF\Framework\Reflection\Interfaces\Reflection;

/**
 * A property context annotation needs the property to be properly built
 */
interface Reflection_Context_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value           string
	 * @param $property        Reflection eg the contextual Reflection_Property or Reflection_Class
	 *                                    object
	 * @param $annotation_name string
	 */
	public function __construct($value, Reflection $property, $annotation_name);

}
