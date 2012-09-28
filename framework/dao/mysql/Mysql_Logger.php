<?php
namespace SAF\Framework;
use AopJoinpoint;

class Mysql_Logger
{

	//----------------------------------------------------------------------------------- $errors_log
	/**
	 * The errors log
	 *
	 * Errors are full text looking like "errno: Error message [SQL Query]".
	 *
	 * @var multitype:string
	 */
	public $errors_log = array();

	//---------------------------------------------------------------------------------- $queries_log
	/**
	 * The queryies log
	 *
	 * All executed queries are logged here.
	 *
	 * @var multitype:string
	 */
	public $queries_log = array();

	//----------------------------------------------------------------------------------- __construct
	private function __construct() {}

	//----------------------------------------------------------------------------------- getInstance
	/**
	 * Get the Mysql_Logger instance
	 *
	 * @return Mysql_Logger
	 */
	public static function getInstance()
	{
		static $instance = null;
		if (!isset($instance)) {
			$instance = new Mysql_Logger();
		}
		return $instance;
	}

	//--------------------------------------------------------------------------------------- onQuery
	/**
	 * Called each time before a mysql_query() call is done : log the query
	 *
	 * @param AopJoinpoint $joinpoint
	 */
	public function onQuery($joinpoint)
	{
		$arguments = $joinpoint->getArguments();
		echo "<div class=\"Mysql logger query\">" . $arguments[0] . "</div>\n";
		$this->queries_log[] = $arguments[0];
	}

	//--------------------------------------------------------------------------------------- onQuery
	/**
	 * Called each time after a mysql_query() call is done : log the error (if some)
	 *
	 * @param AopJoinpoint $joinpoint
	 */
	public function onError(AopJoinpoint $joinpoint)
	{
		$mysqli = $joinpoint->getObject();
		if ($mysqli->errno) {
			
			$arguments = $joinpoint->getArguments();
			$error = $mysqli->errno . ": " . $mysqli->error . "[" . $arguments[0] . "]"; 
			echo "<div class=\"Mysql logger error\">" . $error . "</div>\n";
			$this->errors_log[] = $error;
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		$mysql_logger = self::getInstance();
		aop_add_before("mysqli->query()", array($mysql_logger, "onQuery"));
		aop_add_after("mysqli->query()", array($mysql_logger, "onError"));
	}

}
