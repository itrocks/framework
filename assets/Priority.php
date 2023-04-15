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
	public static function valid(string $value) : bool
	{
		return in_array(
			$value,
			[static::EXCLUDED, static::FIRST, static::INCLUDED, static::LAST],
			true
		);
	}

}
