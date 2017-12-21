<?php
namespace ITRocks\Framework\Exception;

use Exception;
use Throwable;

/**
 * Http_Exception exception
 *
 * Usage: mother class to others exception http codes
 * Each http exception has a Header to http code
 */
class Http_Exception extends Exception
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Http_Exception constructor
	 *
	 * @param $message  string
	 * @param $code     integer
	 * @param $previous Throwable|null
	 */
	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

}
