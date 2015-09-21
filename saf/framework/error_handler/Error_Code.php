<?php
namespace SAF\Framework\Error_Handler;

/**
 * An error code storage and translator
 */
class Error_Code
{

	//------------------------------------------------------------------------------------- $CAPTIONS
	private static $CAPTIONS = [
		E_ALL               => 'all',
		E_COMPILE_ERROR     => 'compile error',
		E_COMPILE_WARNING   => 'compile warning',
		E_CORE_ERROR        => 'core error',
		E_CORE_WARNING      => 'core warning',
		E_DEPRECATED        => 'deprecated',
		E_ERROR             => 'error',
		E_NOTICE            => 'notice',
		E_PARSE             => 'parse',
		E_RECOVERABLE_ERROR => 'recoverable error',
		E_STRICT            => 'strict',
		E_USER_DEPRECATED   => 'user deprecated',
		E_USER_ERROR        => 'user error',
		E_USER_NOTICE       => 'user notice',
		E_USER_WARNING      => 'user warning',
		E_WARNING           => 'warning'
	];

	//----------------------------------------------------------------------------------------- $code
	/**
	 * @var integer
	 */
	public $code;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $code integer
	 */
	public function __construct($code)
	{
		$this->code = $code;
	}

	//--------------------------------------------------------------------------------------- caption
	/**
	 * @return string
	 */
	public function caption()
	{
		return self::$CAPTIONS[$this->code];
	}

}
