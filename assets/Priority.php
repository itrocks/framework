<?php
namespace ITRocks\Framework\Assets;

/**
 * Assets priority
 */
class Priority
{

	//---------------------------------------------------------- Level of priority for assets loading
	const EXCLUDED = 'excluded';
	const FIRST    = 'first';
	const INCLUDED = 'included';
	const LAST     = 'last';

	//----------------------------------------------------------------------------------------- valid
	/**
	 * @param $value string
	 * @return boolean
	 */
	public static function valid($value)
	{
		return in_array(
			$value,
			[static::EXCLUDED, static::FIRST, static::INCLUDED, static::LAST],
			true
		);
	}

}
