<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * The store_name annotation forces the name of the stored field
 */
class Store_Name_Annotation extends Annotation implements Property_Context_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'store_name';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value               ?string
	 * @param $reflection_property Reflection_Property
	 */
	public function __construct(?string $value, Reflection_Property $reflection_property)
	{
		if (!$value) {
			$value = $reflection_property->getName();
		}
		parent::__construct($value);
	}

}
