<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Tools\Encryption;

/**
 * This tells the string property stores encrypted data
 *
 * @example @encrypt sha1
 * @see Encryption class to know which encryptions can be used (eg 'crypt', 'md5', 'sha1')
 */
class Encrypt_Annotation extends Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'encrypt';

}
