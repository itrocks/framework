<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use SAF\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * The storage annotation forces the name of the stored field
 */
class Storage_Annotation extends Annotation implements Property_Context_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value               string
	 * @param $reflection_property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $reflection_property)
	{
		if (!$value) {
			$value = $reflection_property->getName();
		}
		parent::__construct($value);
	}

}
