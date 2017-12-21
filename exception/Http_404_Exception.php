<?php
namespace Itrocks\Framework\Exception;

use Exception;
use Throwable;

/**
 * Class Http_404_Exception
 */
class Http_404_Exception extends Exception
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Http_404_Exception constructor
	 *
	 * @param $message  string
	 * @param $code     int
	 * @param $previous Throwable|null
	 */
	public function __construct($message = "", $code = 404, Throwable $previous = null)
	{
		$this->code    = $code;
		$this->message = $message;
		parent::__construct($message, $code, $previous);
	}

}
