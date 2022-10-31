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
	public int $time_limit;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $time_limit integer Data link query execution time limit in seconds. 0 = no limit
	 */
	public function __construct(int $time_limit = 0)
	{
		// convert second in milliseconds
		$this->time_limit = round($time_limit * 1000);
	}

	//---------------------------------------------------------------------------------------- getSql
	/**
	 * Directive corresponding of your database
	 *
	 * @return string
	 */
	public function getSql() : string
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

	//---------------------------------------------------------------------------- isErrorCodeTimeout
	/**
	 * @param $error_code integer
	 * @param $data_link  Data_Link|null
	 * @return boolean
	 */
	public static function isErrorCodeTimeout(int $error_code, Data_Link $data_link = null) : bool
	{
		if (!$data_link) {
			$data_link = Dao::current();
		}
		if ($data_link instanceof Dao\Mysql\Link) {
			return in_array(
				$error_code, [Mysql\Errors::ER_FILSORT_ABORT, Mysql\Errors::ER_QUERY_TIMEOUT], true
			);
		}
		return false;
	}

}
