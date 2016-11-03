<?php
namespace ITRocks\Framework\Tools;

/**
 * Stringable are objects that can be stored or read as string
 */
interface Stringable
{

	//------------------------------------------------------------------------------------ fromString
	/**
	 * @param $string string
	 * @return self
	 */
	public static function fromString($string);

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString();

}
