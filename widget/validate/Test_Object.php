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

	//--------------------------------------------------------------------------------------- MESSAGE
	const MESSAGE = 'This is not value';

	//------------------------------------------------------------------------------------- $property
	/**
	 * @validate notValidFalse
	 * @validate notValidMessage
	 * @validate validTrue
	 * @var string
	 */
	protected $property = 'its-value';

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
		return self::MESSAGE;
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
