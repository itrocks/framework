<?php
namespace ITRocks\Framework\Dao\Option;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Mysql;
use ITRocks\Framework\Dao\Option;

/**
 * A DAO Max_Execution_Time option
 */
class Time_Limit implements Option
{

	//----------------------------------------------------------------------------------- $time_limit
	/**
	 * @var integer Effective Query execution time limit in milliseconds
	 */
	public $time_limit;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $time_limit integer Data link query execution time limit in seconds. 0 = no limit
	 */
	public function __construct($time_limit = 0)
	{
		// convert second in milliseconds
		$this->time_limit = round($time_limit * 1000);
	}

	//---------------------------------------------------------------------------------- getErrorCode
	/**
	 * @param $data_link Data_Link
	 * @return integer|null
	 */
	public static function getErrorCode(Data_Link $data_link = null)
	{
		if (!$data_link) {
			$data_link = Dao::current();
		}
		if ($data_link instanceof Dao\Mysql\Link) {
			return Mysql\Errors::MAX_EXECUTION_TIME_OUT;
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
			&& $this->time_limit
		) {
			return '/*+ MAX_EXECUTION_TIME(' . $this->time_limit . ') */';
		}

		return '';
	}

}
