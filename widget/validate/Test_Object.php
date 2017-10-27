<?php
namespace ITRocks\Framework\Widget\Validate;

/**
 * Validate widget testing
 *
 * @validate notValidFalse
 * @validate notValidMessage
 * @validate validTrue
 */
class Test_Object
{

	//----------------------------------------------------------------------------- NOT_VALID_MESSAGE
	const NOT_VALID_MESSAGE = 'This is not value';

	//----------------------------------------------------------------------------- NOT_VALID_DYNAMIC
	const NOT_VALID_DYNAMIC = 'This is dynamic';

	//------------------------------------------------------------------------------------- $property
	/**
	 * @validate notValidFalse
	 * @validate notValidMessage
	 * @validate validTrue
	 * @var string
	 */
	protected $property = 'its-value';

	//------------------------------------------------------------------------------- notValidDynamic
	/**
	 * A validation function that returns true
	 *
	 * @return boolean
	 */
	public static function notValidDynamic()
	{
		return self::NOT_VALID_DYNAMIC;
	}

	//--------------------------------------------------------------------------------- notValidFalse
	/**
	 * A validation function that returns false
	 *
	 * @return boolean
	 */
	public function notValidFalse()
	{
		return false;
	}

	//------------------------------------------------------------------------------- notValidMessage
	/**
	 * A validation function that returns an error message
	 *
	 * @return string
	 */
	public function notValidMessage()
	{
		return self::NOT_VALID_MESSAGE;
	}

	//------------------------------------------------------------------------------------- validTrue
	/**
	 * A validation function that returns true
	 *
	 * @return boolean
	 */
	public function validTrue()
	{
		return true;
	}

}
