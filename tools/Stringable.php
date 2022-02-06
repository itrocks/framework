<?php
namespace ITRocks\Framework\Tools;

/**
 * Stringable are objects that can be stored or read as string
 */
interface Stringable
{

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string;

	//------------------------------------------------------------------------------------ fromString
	/**
	 * @param $string string
	 * @return static
	 */
	public static function fromString(string $string) : static;

}
