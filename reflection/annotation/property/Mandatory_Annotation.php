<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces;

/**
 * The mandatory annotation validator
 */
class Mandatory_Annotation extends Boolean_Annotation implements Property_Context_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'mandatory';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    bool|null|string
	 * @param $property Interfaces\Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct(bool|null|string $value, Interfaces\Reflection_Property $property)
	{
		if (!isset($value)) {
			$type  = $property->getType();
			$value = !(
				$type->allowsNull() || $type->isBoolean() || $type->isString() || $type->isDateTime()
			);
		}
		parent::__construct($value);
	}

}
