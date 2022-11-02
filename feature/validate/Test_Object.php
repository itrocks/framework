<?php
namespace ITRocks\Framework\Feature\Validate;

/**
 * Validate widget testing
 *
 * @validate notValidFalse
 * @validate notValidMessage
 * @validate validTrue
 */
class Test_Object
{

	//----------------------------------------------------------------------------- NOT_VALID_DYNAMIC
	const NOT_VALID_DYNAMIC = 'This is dynamic';

	//----------------------------------------------------------------------------- NOT_VALID_MESSAGE
	const NOT_VALID_MESSAGE = 'This is not a value';

	//------------------------------------------------------------------------------------- $property
	/**
	 * @validate notValidFalse
	 * @validate notValidMessage
	 * @validate validTrue
	 * @var string
	 */
	protected string $property = 'its-value';

	//------------------------------------------------------------------------------- notValidDynamic
	/**
	 * A validation function that returns an equivalent of true
	 *
	 * @return string
	 */
	public static function notValidDynamic() : string
	{
		return self::NOT_VALID_DYNAMIC;
	}

	//--------------------------------------------------------------------------------- notValidFalse
	/**
	 * A validation function that returns false
	 *
	 * @return false
	 */
	public function notValidFalse() : bool
	{
		return false;
	}

	//------------------------------------------------------------------------------- notValidMessage
	/**
	 * A validation function that returns an error message
	 *
	 * @return string
	 */
	public function notValidMessage() : string
	{
		return self::NOT_VALID_MESSAGE;
	}

	//------------------------------------------------------------------------------------- validTrue
	/**
	 * A validation function that returns true
	 *
	 * @return true
	 */
	public function validTrue() : bool
	{
		return true;
	}

}
