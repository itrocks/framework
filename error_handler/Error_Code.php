<?php
namespace ITRocks\Framework\Error_Handler;

/**
 * An error code storage and translator
 */
class Error_Code
{

	//--------------------------------------------------------------------------------------- UNKNOWN
	/** Unknown error code message */
	const UNKNOWN = 'unknown';

	//------------------------------------------------------------------------------------- $CAPTIONS
	/**
	 * Captions constants
	 *
	 * @var string[]
	 */
	private const CAPTIONS = [
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
		E_WARNING           => 'warning',
		self::UNKNOWN       => 'unknown'
	];

	//----------------------------------------------------------------------------------------- $code
	/**
	 * @var integer
	 */
	public int $code;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $code integer
	 */
	public function __construct(int $code)
	{
		$this->code = $code;
	}

	//--------------------------------------------------------------------------------------- caption
	/**
	 * @return string
	 */
	public function caption() : string
	{
		return self::CAPTIONS[$this->code] ?? self::UNKNOWN;
	}

	//--------------------------------------------------------------------------------------- isFatal
	/**
	 * @return boolean
	 */
	public function isFatal() : bool
	{
		return in_array(
			$this->code,
			[E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE, E_RECOVERABLE_ERROR, E_USER_ERROR]
		);
	}

}
