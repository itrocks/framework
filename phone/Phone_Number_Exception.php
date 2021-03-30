<?php
namespace ITRocks\Framework\Phone;

use Exception;

class Phone_Number_Exception extends Exception
{
	//----------------------------------------------- Different error type when adding a phone number
	public const ERROR_TYPES = [
		'Invalid country code',
		'This is not a number',
		'Number is too short',
		'Number is too short',
		'Number is too long'
	];

	//----------------------------------------------------------------------------------- $error_type
	/**
	 * @var int
	 */
	private $error_type;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(int $error_type, string $message)
	{
		parent::__construct($message);
		$this->error_type = $error_type;
	}

	//---------------------------------------------------------------------------------- getErrorType
	public function getErrorType(): ?string
	{
		return self::ERROR_TYPES[$this->error_type] ?? null;
	}
}
