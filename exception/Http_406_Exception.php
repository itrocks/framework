<?php
namespace itrocks\framework\exception;

use Exception;
use Throwable;

/**
 * Class Http_406_Exception
 *
 */
class Http_406_Exception extends Exception
{

	/**
	 * Http_406_Exception constructor.
	 * @param string $message
	 * @param int $code
	 * @param Throwable|null $previous
	 */
	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		$this->message = $message;
		$this->code    = $code;
		parent::__construct($message, $code, $previous);
		header('HTTP/1.1 406 Not Acceptable', true, 406);
	}

}
