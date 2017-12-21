<?php
namespace ITRocks\Framework\Exception;

use Exception;
use Throwable;

/**
 * Http_400_Exception Bad Request
 */
class Http_400_Exception extends Exception
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Http_400_Exception constructor
	 *
	 * @param $message  string
	 * @param $code     int
	 * @param $previous Throwable|null
	 */
	public function __construct($message = '', $code = 400, Throwable $previous = null)
	{
		$this->code    = $code;
		$this->message = empty($message)
			? 'The request was invalid or cannot be otherwise served.'
			: $message;
		parent::__construct($this->message, $code, $previous);
	}

}
