<?php
namespace ITRocks\Framework\Dao\Option;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Option;

/**
 * A DAO Max_Execution_Time option
 */
class Max_Execution_Time implements Option
{

	//------------------------------------------------------------------------------ ERROR_CODE_MYSQL
	const ERROR_CODE_MYSQL = 256;

	//----------------------------------------------------------------------------------------- $time
	private $time;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Max_Execution_Time constructor.
	 * @param int $time in seconds
	 */
	public function __construct($time = 0)
	{
		$this->time = $time * 1000;//convert second in milliseconds
	}

	//---------------------------------------------------------------------------------- getErrorCode
	/**
	 * @return int|null
	 */
	public static function getErrorCode()
	{
		$current = Dao::current();
		if (is_a($current, Dao\Mysql\Link::class)) {
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

		//MySQL 5.7.4 introduces the ability to set server side execution time limits.
		if ($current instanceof Dao\Mysql\Link
			&& mysqli_get_server_version($current->getConnection()) >= 50704
		) {
			return '/*+ MAX_EXECUTION_TIME(' . $this->time . ') */';
		}
		return '';
	}

}
