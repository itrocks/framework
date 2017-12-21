<?php
namespace ITRocks\Framework\Exception;

use Exception;
use Throwable;

/**
 * Class Http_406_Exception
 */
class Http_406_Exception extends Exception
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Http_406_Exception constructor
	 *
	 * @param $message  string
	 * @param $code     int
	 * @param $previous Throwable|null
	 */
	public function __construct($message = "", $code = 406, Throwable $previous = null)
	{
		$this->code    = $code;
		$this->message = $message;
		parent::__construct($message, $code, $previous);
	}

}
