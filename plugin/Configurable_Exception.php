<?php
namespace ITRocks\Framework\Plugin;

use Exception;
use ITRocks\Framework\Plugin;
use Throwable;

/**
 * Exception that should be thrown on every error found on a Configurable plugin configuration
 *
 * @see Configurable::__construct
 */
class Configurable_Exception extends Exception
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $plugin   Plugin
	 * @param $message  string
	 * @param $code     integer
	 * @param $previous Throwable|null
	 */
	public function __construct(
		Plugin $plugin, string $message = '', int $code = 0, Throwable $previous = null
	) {
		$message = '[' . get_class($plugin) . ']' . SP . $message;
		parent::__construct($message, $code, $previous);
	}

}
