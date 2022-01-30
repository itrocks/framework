<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * An alias is an alternative display of the property name, in some cases
 *
 * ie when an object is integrated to its container object, for an output view
 *
 * @example
 * If the city object of an address is integrated 'simple', the name of the city should be aliased
 * to 'city'
 */
class Alias_Annotation extends Annotation implements Property_Context_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'alias';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    ?string
	 * @param $property Reflection_Property
	 */
	public function __construct(?string $value, Reflection_Property $property)
	{
		if (!$value) {
			$value = $property->getName();
		}
		parent::__construct($value);
	}

}
