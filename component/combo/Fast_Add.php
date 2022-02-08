<?php
namespace ITRocks\Framework\Component\Combo;

/**
 * Implement fromString and an object will be able to be created from a string
 *
 * Adding this interface to a class activates the fast-create of an object from a text typed into a
 * combo that does not match any existing object
 */
interface Fast_Add
{

	//------------------------------------------------------------------------------------ fromString
	/**
	 * @param $string string
	 * @return ?static
	 */
	public static function fromString(string $string) : ?static;

}
