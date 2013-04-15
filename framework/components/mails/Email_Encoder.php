<?php
namespace SAF\Framework;

if (!@include_once("framework/vendor/pear/Mail/mime.php")) {
	@include_once "/usr/share/php/Mail/mime.php";
}

/**
 * Encodes MIME emails
 *
 * This offers a SAF interface to the PHP PEAR Mail_Mime package
 * To install it on a Debian Linux server : apt-get install php-mail-mime
 */
abstract class Email_Encoder
{

	//---------------------------------------------------------------------------------------- encode
	/**
	 * Encodes an email into MIME format
	 *
	 * @param Email $email
	 * @return string
	 */
	public static function encode(Email $email)
	{

	}

}
