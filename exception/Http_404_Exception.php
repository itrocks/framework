<?php
namespace Itrocks\Framework\Exception;

use Exception;
use Throwable;

/**
 * Class Http_404_Exception
 *
 */
class Http_404_Exception extends Exception
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Http_404_Exception constructor.
	 * @param string $message
	 * @param int $code
	 * @param Throwable|null $previous
	 */
	public function __construct($message = "", $code = 404, Throwable $previous = null)
	{
		$this->message = $message;
		$this->code    = $code;
    parent::__construct($message, $code, $previous);
	}

}
