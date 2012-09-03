<?php

class Mysql_Logger
{

	//------------------------------------------------------------------------------------- $instance
	/**
	 * @var Mysql_Logger
	 */
	private static $instance;

	//----------------------------------------------------------------------------------- $errors_log
	/**
	 * @var multitype:string
	 */
	public $errors_log;

	//---------------------------------------------------------------------------------- $queries_log
	/**
	 * @var multitype:string
	 */
	public $queries_log;

	//----------------------------------------------------------------------------------- __construct
	private function __construct()
	{
	}

	//----------------------------------------------------------------------------------- getInstance
	public static function getInstance()
	{
		if (!Mysql_Logger::$instance) {
			Mysql_Logger::$instance = new Mysql_Logger();
		}
		return Mysql_Logger::$instance;
	}

	//--------------------------------------------------------------------------------------- onQuery
	/**
	 * @param AopJoinPoint $joinpoint
	 */
	public function onQuery($joinpoint)
	{
		$arguments = $joinpoint->getArguments();
		echo "<div class=\"Mysql_Logger_onQuery\">" . $arguments[0] . "</div>\n";
	}

	//--------------------------------------------------------------------------------------- onQuery
	/**
	 * @param AopJoinPoint $joinpoint
	 */
	public function onError($joinpoint)
	{
		if (mysql_errno()) {
			echo "<div class=\"Mysql_Logger_onError\">"
				. mysql_errno() . " : " . mysql_error()
				. "</div>\n";
		}
	}

}

aop_add_before("mysql_query()", array(Mysql_Logger::getInstance(), "onQuery"));
aop_add_after("mysql_query()", array(Mysql_Logger::getInstance(), "onError"));
