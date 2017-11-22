<?php
namespace itrocks\framework\exception;

use Exception;
use Throwable;

/**
 * Http_400_Exception Bad Request
 *
 */
class Http_400_Exception extends Exception
{

	/**
	 * Http_400_Exception constructor.
	 * @param string $message
	 * @param int $code
	 * @param Throwable|null $previous
	 */
	public function __construct($message = '', $code = 0, Throwable $previous = null)
	{
		$this->message = ($message == '' || $message == null
			? 'The request was invalid or cannot be otherwise served.'
			: $message);
		$this->code    = $code;
    parent::__construct($this->message, $code, $previous);
		header('HTTP/1.1 400 Bad Request', true, 400);
	}

}
