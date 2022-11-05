<?php
namespace ITRocks\Framework\Tools;

use Exception;

/**
 * Class Period_Exception
 */
class Date_Interval_Exception extends Exception
{

	//--------------------------------------------------------------------------------------- MESSAGE
	const MESSAGE
		= 'Impossible to know the real number of days for an interval not created with DateTime::diff 
		@see Notes at https://php.net/manual/fr/dateinterval.format.php ';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Date_Interval_Exception constructor.
	 */
	public function __construct()
	{
		parent::__construct(static::MESSAGE);
	}

}
