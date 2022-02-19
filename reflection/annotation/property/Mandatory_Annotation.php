<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Feature\Validate\Result;
use ITRocks\Framework\History\Has_History;
use ITRocks\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Reflection_Property;

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
			$value = $property->getAnnotation('composite')->value
				|| $property->getAnnotation('link_composite')->value;
		}
		parent::__construct($value);
	}

}
