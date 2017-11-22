<?php
namespace ITRocks\Framework\Exception;

use Exception;
use Throwable;

/**
 * Http_Exception exception.
 * Usage: mother class to others exception http codes.
 * Each http exception has a Header to http code
 *
 */
class Http_Json_Exception extends Exception
{

	/**
	 * Http_Json_Exception constructor.
	 * @param string $message
	 * @param int $code
	 * @param Throwable|null $previous
	 */
	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
		header('Content-Type: application/json; charset=utf-8');
	}
}
