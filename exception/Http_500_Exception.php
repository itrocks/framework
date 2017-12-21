<?php
namespace ITRocks\Framework\Exception;

use Exception;
use Throwable;

/**
 * Class Http_500_Exception
 */
class Http_500_Exception extends Exception
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Http_500_Exception constructor
	 *
	 * @param $message  string
	 * @param $code     int
	 * @param $previous Throwable|null
	 */
	public function __construct($message = "", $code = 500, Throwable $previous = null)
	{
		$this->code    = $code;
		$this->message = $message;
		parent::__construct($message, $code, $previous);
	}

}
