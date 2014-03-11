<?php
namespace SAF\Framework;

/**
 * This tells the string property stores crypted password
 *
 * @example @password sha1
 * @see Password class to know which encryptions can be used (ie "crypt", "md5", "sha1")
 */
class Password_Annotation extends Annotation
{

	//---------------------------------------------------------------------------------------- $value
	/**
	 * Default annotation constructor receive the full doc text content
	 *
	 * Annotation class will have to parse it ie for several parameters or specific syntax, or if they want to store specific typed or calculated value
	 *
	 * @param $value string
	 */
	public function __construct($value)
	{
		if (isset($value) && !$value) {
			$value = 'sha1';
		}
		parent::__construct($value);
	}

}
