<?php
namespace ITRocks\Framework\Exception;

use Exception;
use Throwable;

/**
 * Class Http_403_Exception
 */
class Http_403_Exception extends Exception
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Http_403_Exception constructor
	 *
	 * @param $message  string
	 * @param $code     int
	 * @param $previous Throwable|null
	 */
	public function __construct($message = '', $code = 403, Throwable $previous = null)
	{
		$this->code    = $code;
		$this->message = empty($message) ? 'FORBIDDEN: The request has been refused' : $message;
		parent::__construct($this->message, $code, $previous);
	}

}
