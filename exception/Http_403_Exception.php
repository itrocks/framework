<?php
namespace ITRocks\Framework\Exception;

use Exception;
use Throwable;

/**
 * Class Http_403_Exception
 *
 */
class Http_403_Exception extends Exception
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Http_403_Exception constructor.
	 * @param string $message
	 * @param int $code
	 * @param Throwable|null $previous
	 */
	public function __construct($message = '', $code = 403, Throwable $previous = null)
	{
		$this->message = ($message == '' || $message == null
			? 'FORBIDDEN: The request has been refused'
			: $message);
		$this->code    = $code;
    parent::__construct($this->message, $code, $previous);
	}

}
