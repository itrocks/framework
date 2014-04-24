<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use SAF\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * An alias is an alternative display of the property name, on some cases
 *
 * ie when an object is integrated to it's container object, for an output view
 *
 * @example
 * If the city object of an address is integrated 'simple', the name of the city should be aliased
 * to 'city'
 */
class Alias_Annotation extends Annotation implements Property_Context_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string
	 * @param $property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $property)
	{
		if (!$value) {
			$value = $property->getName();
		}
		parent::__construct($value);
	}

}
