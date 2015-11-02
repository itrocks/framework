<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Tools\Encryption;

/**
 * This tells the string property stores crypted password
 *
 * @example @password sha1
 * @see Password class to know which encryptions can be used (ie 'crypt', 'md5', 'sha1')
 */
class Password_Annotation extends Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Default annotation constructor receive the full doc text content
	 *
	 * Annotation class will have to parse it ie for several parameters or specific syntax, or if they
	 * want to store specific typed or calculated value
	 *
	 * @param $value string any value from SAF\Framework\Tools\Encryption constants
	 */
	public function __construct($value)
	{
		if (isset($value) && !$value) {
			$value = Encryption::SHA1;
		}
		parent::__construct($value);
	}

}
