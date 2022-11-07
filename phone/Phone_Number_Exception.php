<?php
namespace ITRocks\Framework\Phone;

use Exception;

class Phone_Number_Exception extends Exception
{

	//----------------------------------------------------------------------------------- ERROR_TYPES
	/**
	 * Different error types when a phone number is validated
	 */
	public const ERROR_TYPES = [
		'Invalid country code',
		'This is not a number',
		'Number is too short',
		'Number is too short',
		'Number is too long'
	];

	//----------------------------------------------------------------------------------- $error_type
	/**
	 * @var integer
	 */
	private int $error_type;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $error_type integer
	 * @param $message    string
	 */
	public function __construct(int $error_type, string $message)
	{
		parent::__construct($message);
		$this->error_type = $error_type;
	}

	//---------------------------------------------------------------------------------- getErrorType
	/**
	 * @return ?string
	 */
	public function getErrorType() : ?string
	{
		return self::ERROR_TYPES[$this->error_type] ?? null;
	}

}
