<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

/**
 * This tells the string property stores encrypted password
 *
 * @example @password sha1
 */
class Password_Annotation extends Encrypt_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'password';

}
