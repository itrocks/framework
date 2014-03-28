<?php
namespace SAF\Framework;

/**
 * An alias is an alternative display of the property name, on some cases
 *
 * ie when an object is integrated to it's container object, for an output view
 *
 * @example
 * If the city object of an address is integrated 'simple', the name of the city should be aliased
 * to 'city'
 */
class Alias_Annotation extends Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value               string
	 * @param $reflection_property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $reflection_property)
	{
		if (!$value) {
			$value = $reflection_property->name;
		}
		parent::__construct($value);
	}

}
