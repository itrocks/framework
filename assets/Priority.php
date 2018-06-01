<?php
namespace ITRocks\Framework\Assets;

/**
 * Class Assets_File_Part
 */
class Priority
{

	//-------------------------------------------------------------------------------------- EXCLUDED
	const EXCLUDED = 'excluded';

	//----------------------------------------------------------------------------------------- FIRST
	const FIRST = 'first';

	//-------------------------------------------------------------------------------------- INCLUDED
	const INCLUDED = 'included';

	//------------------------------------------------------------------------------------------ LAST
	const LAST = 'last';

	//----------------------------------------------------------------------------------------- valid
	/**
	 * @param $value string
	 * @return boolean
	 */
	public static function valid($value)
	{
		return static::EXCLUDED === $value
			|| static::FIRST === $value
			|| static::INCLUDED === $value
			|| static::LAST === $value;
	}

}
