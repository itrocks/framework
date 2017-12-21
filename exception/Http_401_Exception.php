<?php
namespace ITRocks\Framework\Exception;

use Exception;
use Throwable;

/**
 * Class Http_404_Exception
  */
class Http_401_Exception extends Exception
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Http_401_Exception constructor
	 *
	 * @param $message  string
	 * @param $code     int
	 * @param $previous Throwable|null
	 */
	public function __construct($message = '', $code = 401, Throwable $previous = null)
	{
		$this->code    = $code;
		$this->message = empty($message)
			? 'The request requires user authentication. Bad or missing authentication'
			: $message;
		parent::__construct($this->message, $code, $previous);
	}

}
