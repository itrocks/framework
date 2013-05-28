<?php
namespace SAF\Framework;

/** @noinspection PhpIncludeInspection */
if (!@include_once("framework/vendor/pear/Net/POP3.php")) {
	@include_once "/usr/share/php/Net/POP3.php";
}

/**
 * Receives emails
 *
 * This offers a SAF interface to the PHP PEAR Net_POP3 package
 * To install it on a Debian Linux server : apt-get install php-pear & pear install Net_POP3
 */
class Email_Receiver
{

}
