<?php
namespace SAF\Framework;

if (!@include_once(__DIR__ . '../../vendor/pear/Mail/mimeDecode.php')) {
	@include_once '/usr/share/php/Mail/mimeDecode.php';
}

/**
 * Decodes MIME emails
 *
 * This offers a SAF interface to the PHP PEAR Mail_mimeDecode package
 * To install it on a Debian Linux server : apt-get install php-mail-mimedecode
 */
class Email_Decoder
{

}
