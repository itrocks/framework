<?php
namespace ITRocks\Framework\Dao\Mysql;

use Exception;

/**
 * Mysql error exception
 *
 * @package ITRocks\Framework\Dao\Mysql
 */
class Mysql_Error_Exception extends Exception
{

	//---------------------------------------------------------------------------------------- $query
	/**
	 * @var string
	 */
	public string $query;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Mysql error exception constructor
	 *
	 * @param $error_number  integer
	 * @param $error_message string
	 * @param $query         string
	 */
	public function __construct($error_number, $error_message, $query)
	{
		parent::__construct($error_message . ' in query [' . $query . ']', $error_number);
		$this->query = $query;
	}

}
