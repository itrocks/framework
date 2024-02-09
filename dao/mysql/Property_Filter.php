<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * Filters a buildable property
 */
trait Property_Filter
{

	//--------------------------------------------------------------------------------- STRING_OBJECT
	/**
	 * @var string[]
	 */
	protected static array $STRING_OBJECT = [
		Store_Annotation::GZ,
		Store_Annotation::JSON,
		Store_Annotation::SERIALIZE,
		Store_Annotation::STRING
	];

	//-------------------------------------------------------------------------- $excluded_properties
	/**
	 * Excluded properties names
	 *
	 * For classes with a link annotation, all properties names from the linked parent class
	 * and its own parents are excluded.
	 *
	 * @var string[]
	 */
	protected array $excluded_properties;

	//-------------------------------------------------------------------------------- filterProperty
	/**
	 * @param $property Reflection_Property
	 * @return boolean
	 */
	protected function filterProperty(Reflection_Property $property) : bool
	{
		$type = $property->getType();
		return
			!in_array($property->name, $this->excluded_properties, true)
			&& (
				$type->isMultipleString()
				|| !$type->isMultiple()
				|| in_array(Store_Annotation::of($property)->value, static::$STRING_OBJECT, true)
			)
			&& !$property->isStatic()
			&& (
				!$property->getAnnotation('component')->value
				|| in_array(Store_Annotation::of($property)->value, static::$STRING_OBJECT, true)
			)
			&& !Store_Annotation::of($property)->isFalse();
	}

}
