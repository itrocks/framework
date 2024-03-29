<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * This tells that the property can take the null value as a valid value (default is false)
 */
class Null_Annotation extends Boolean_Annotation implements Property_Context_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'null';

	//------------------------------------------------------------------------------------------ NULL
	const NULL = 'null';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    bool|null|string
	 * @param $property Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct(bool|null|string $value, Reflection_Property $property)
	{
		parent::__construct($value);
		// default value for @null is true when the property links to a non-mandatory object
		if (
			(
				!isset($value)
				&& !$this->value
				&& !Mandatory::of($property)->value
				&& Link_Annotation::of($property)->isObject()
			)
			|| $property->getType()->allowsNull()
		) {
			$this->value = true;
		}
	}

}
