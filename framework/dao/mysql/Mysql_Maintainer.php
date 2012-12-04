<?php
namespace SAF\Framework;
use AopJoinpoint;

class Mysql_Maintainer
{

	//--------------------------------------------------------------------------------- onMysqliQuery
	private static function addTable($table_name, $class_name)
	{
		echo "add table $table_name in context $class_name<br>";
	}

	//--------------------------------------------------------------------------------- onMysqliQuery
	public static function onMysqliQuery(AopJoinpoint $joinpoint)
	{
		$mysqli = $joinpoint->getObject();
		if ($mysqli->errno && isset($mysqli->context_class)) {
			$query = $joinpoint->getArguments()[0];
			switch ($mysqli->errno) {
				case Mysql_Errors::ER_NO_SUCH_TABLE:
					self::addTable(self::parseTableNameFromError($mysqli->error), $mysqli->context_class);
					break;
			}
		}
	}

	//----------------------------------------------------------------------- parseTableNameFromError
	private static function parseTableNameFromError($error)
	{
		$i = strpos($error, "'") + 1;
		$j = strpos($error, "'", $i);
		return substr($error, $i, $j - $i);
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after", "mysqli->query()", array(__CLASS__, "onMysqliQuery"));
	}

}
