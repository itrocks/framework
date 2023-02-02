<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * Filters a buildable property
 */
trait Property_Filter
{

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
			&& !$property->isStatic()
			&& !($store = Store::of($property))->isFalse()
			&& ($store->isString() || $type->isMultipleString() || !$type->isMultiple())
			&& ($store->isString() || !$property->getAnnotation('component')->value);
	}

}
