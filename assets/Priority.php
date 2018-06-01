<?php
namespace ITRocks\Framework\Assets;

/**
 * Class Assets_File_Part
 */
class Priority
{

	//-----------------------------------------------------------Level of priority for assets loading
	const EXCLUDED = 'excluded';
	const FIRST = 'first';
	const INCLUDED = 'included';
	const LAST = 'last';

	//----------------------------------------------------------------------------------------- valid
	/**
	 * @param $value string
	 * @return boolean
	 */
	public static function valid($value)
	{
		return (static::EXCLUDED === $value)
			|| (static::FIRST === $value)
			|| (static::INCLUDED === $value)
			|| (static::LAST === $value);
	}

}
