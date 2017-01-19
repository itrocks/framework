<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Tools\Encryption;

/**
 * This tells the string property stores crypted password
 *
 * @example @password sha1
 * @see Encryption class to know which encryptions can be used (eg 'crypt', 'md5', 'sha1')
 */
class Password_Annotation extends Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'password';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Default annotation constructor receive the full doc text content
	 *
	 * Annotation class will have to parse it ie for several parameters or specific syntax, or if they
	 * want to store specific typed or calculated value
	 *
	 * @param $value string any value from ITRocks\Framework\Tools\Encryption constants
	 */
	public function __construct($value)
	{
		if (isset($value) && !$value) {
			$value = Encryption::SHA1;
		}
		parent::__construct($value);
	}

}
