<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Reflection\Annotation;

/**
 * Link annotation defines which kind of link is defined for an object or array of objects property
 *
 * Value can be 'All', 'Collection', 'DateTime', 'Map', 'Object'
 */
class Link_Annotation extends Annotation
{

	//--------------------------------------------------------------------------------- $value values
	const ALL        = 'All';
	const COLLECTION = 'Collection';
	const DATETIME   = 'DateTime';
	const MAP        = 'Map';
	const OBJECT     = 'Object';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param string $value
	 */
	public function __construct($value)
	{
		$possibles = [self::ALL, self::COLLECTION, self::DATETIME, self::MAP, self::OBJECT];
		if (!empty($value) && !in_array($value, $possibles)) {
			trigger_error(
				'@link ' . $value . ' is a bad value : only ' . join(', ', $possibles) . ' can be used',
				E_USER_ERROR
			);
			$value = '';
		}
		parent::__construct($value);
	}

	//----------------------------------------------------------------------------------------- isAll
	/**
	 * @return boolean
	 */
	public function isAll()
	{
		return $this->value === self::ALL;
	}

	//---------------------------------------------------------------------------------- isCollection
	/**
	 * @return boolean
	 */
	public function isCollection()
	{
		return $this->value === self::COLLECTION;
	}

	//------------------------------------------------------------------------------------ isDateTime
	/**
	 * @return boolean
	 */
	public function isDateTime()
	{
		return $this->value === self::DATETIME;
	}

	//----------------------------------------------------------------------------------------- isMap
	/**
	 * @return boolean
	 */
	public function isMap()
	{
		return $this->value === self::MAP;
	}

	//------------------------------------------------------------------------------------ isMultiple
	/**
	 * @return boolean
	 */
	public function isMultiple()
	{
		return in_array($this->value, [self::ALL, self::COLLECTION, self::MAP]);
	}

	//-------------------------------------------------------------------------------------- isObject
	/**
	 * @return boolean
	 */
	public function isObject()
	{
		return $this->value === self::OBJECT;
	}

}
