<?php
namespace ITRocks\Framework\Exception;

use Exception;
use Throwable;

/**
 * Http_400_Exception Bad Request
 *
 */
class Http_400_Exception extends Exception
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Http_400_Exception constructor.
	 * @param string $message
	 * @param int $code
	 * @param Throwable|null $previous
	 */
	public function __construct($message = '', $code = 400, Throwable $previous = null)
	{
		$this->message = ($message == '' || $message == null
			? 'The request was invalid or cannot be otherwise served.'
			: $message);
		$this->code    = $code;
    parent::__construct($this->message, $code, $previous);
	}

}
