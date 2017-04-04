<?php
namespace ITRocks\Framework\Dao\Option;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Option;

/**
 * A DAO Max_Execution_Time option
 */
class Time_Limit implements Option
{

	//------------------------------------------------------------------------------ ERROR_CODE_MYSQL
	const ERROR_CODE_MYSQL = 256;

	//----------------------------------------------------------------------------------------- $time
	private $time_limit;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $time_limit integer Data link query execution time limit in seconds
	 */
	public function __construct($time_limit = 0)
	{
		// convert second in milliseconds
		$this->time_limit = $time_limit * 1000;
	}

	//---------------------------------------------------------------------------------- getErrorCode
	/**
	 * @return integer|null
	 */
	public static function getErrorCode()
	{
		$current = Dao::current();
		if ($current instanceof Dao\Mysql\Link) {
			return self::ERROR_CODE_MYSQL;
		}
		return null;
	}

	//---------------------------------------------------------------------------------------- getSql
	/**
	 * Directive corresponding of your database
	 * @return string
	 */
	public function getSql()
	{
		$current = Dao::current();

		// MySQL 5.7.4 introduces the ability to set server side execution time limits
		if (
			($current instanceof Dao\Mysql\Link)
			&& ($current->getConnection()->server_version >= 50704)
		) {
			return '/*+ MAX_EXECUTION_TIME(' . $this->time_limit . ') */';
		}

		return '';
	}

}
