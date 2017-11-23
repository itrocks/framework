<?php
namespace itrocks\framework\exception;

use Exception;
use Throwable;

/**
 * Class Http_404_Exception
 *
 */
class Http_401_Exception extends Exception
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Http_401_Exception constructor.
	 * @param string $message
	 * @param int $code
	 * @param Throwable|null $previous
	 */
	public function __construct($message = '', $code = 401, Throwable $previous = null)
	{
		$this->message = ($message == '' || $message == null
			? 'The request requires user authentication. Bad or missing authentication'
			: $message);
		$this->code    = $code;
    parent::__construct($this->message, $code, $previous);
	}

}
