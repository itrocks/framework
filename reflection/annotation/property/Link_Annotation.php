<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Has_Is;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Link annotation defines which kind of link is defined for an object or array of objects property
 *
 * Value can be 'All', 'Collection', 'DateTime', 'Map', 'Object'
 */
class Link_Annotation extends Annotation implements Property_Context_Annotation
{
	use Has_Is;

	//--------------------------------------------------------------------------------- $value values
	const ALL        = 'All';
	const ANNOTATION = 'link';
	const COLLECTION = 'Collection';
	const DATETIME   = 'DateTime';
	const MAP        = 'Map';
	const OBJECT     = 'Object';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    ?string
	 * @param $property Reflection_Property
	 */
	public function __construct(?string $value, Reflection_Property $property)
	{
		$possibles = [self::ALL, self::COLLECTION, self::DATETIME, self::MAP, self::OBJECT];
		if (
			empty($value)
			&& $property->getType()->isClass()
			&& $property->getFinalClass()->getAnnotation('stored')->value
		) {
			$value = $this->guessValue($property);
		}
		if (!empty($value) && !in_array($value, $possibles)) {
			trigger_error(
				'@link ' . $value . ' is a bad value : only ' . join(', ', $possibles) . ' can be used',
				E_USER_ERROR
			);
		}
		parent::__construct($value);
	}

	//------------------------------------------------------------------------------------ guessValue
	/**
	 * Guess value for link using the type of the property (var)
	 * - property is a Date_Time (or a child class) : link will be 'DateTime'
	 * - property is a single object : link will be 'Object'
	 * - property is a collection object with 'var Object[] All' : link will be 'All'
	 * - property is a collection object with 'var Object[] Collection' : link will be 'Collection'
	 * - property is a collection object with 'var Object[] Map' : link will be 'Map'
	 * - property is a collection object without telling anything :
	 *   - link will be 'Collection' if the object is a Component
	 *   - link will be 'Map' if the object is not a Component
	 *
	 * Notice : 'link' and 'var' are to be considered as 'property annotation link' and
	 * 'property annotation var' here.
	 *
	 * @param $property Reflection_Property
	 * @return string returned guessed value for @link
	 */
	private function guessValue(Reflection_Property $property) : string
	{
		if ($property->getType()->isMultiple()) {
			/** @var $var_annotation Var_Annotation */
			$value = lParse($property->getAnnotation('var'), SP);
			if (empty($value)) {
				$value = isA($property->getType()->getElementTypeAsString(), Component::class)
					? self::COLLECTION
					: self::MAP;
			}
		}
		else {
			$value = isA($property->getType()->asString(), Date_Time::class)
				? self::DATETIME
				: self::OBJECT;
		}
		return $value;
	}

	//----------------------------------------------------------------------------------------- isAll
	/**
	 * @return boolean
	 */
	public function isAll() : bool
	{
		return $this->value === self::ALL;
	}

	//---------------------------------------------------------------------------------- isCollection
	/**
	 * @return boolean
	 */
	public function isCollection() : bool
	{
		return $this->value === self::COLLECTION;
	}

	//------------------------------------------------------------------------------------ isDateTime
	/**
	 * @return boolean
	 */
	public function isDateTime() : bool
	{
		return $this->value === self::DATETIME;
	}

	//----------------------------------------------------------------------------------------- isMap
	/**
	 * @return boolean
	 */
	public function isMap() : bool
	{
		return $this->value === self::MAP;
	}

	//------------------------------------------------------------------------------------ isMultiple
	/**
	 * @return boolean
	 */
	public function isMultiple() : bool
	{
		return $this->is(self::ALL, self::COLLECTION, self::MAP);
	}

	//-------------------------------------------------------------------------------------- isObject
	/**
	 * @return boolean
	 */
	public function isObject() : bool
	{
		return $this->value === self::OBJECT;
	}

}
