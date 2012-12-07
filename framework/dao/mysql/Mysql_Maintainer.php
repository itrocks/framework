<?php
namespace SAF\Framework;
use AopJoinpoint;
use mysqli;

class Mysql_Maintainer
{

	//--------------------------------------------------------------------------------- onMysqliQuery
	private static function addTable(mysqli $mysqli, $class_name)
	{
		$class_table = Mysql_Table_Builder_Class::build($class_name);
		$query = (new Sql_Create_Table_Builder($class_table))->build();
		$mysqli->query($query);
	}

	//--------------------------------------------------------------------------------- onMysqliQuery
	public static function onMysqliQuery(AopJoinpoint $joinpoint)
	{
		$mysqli = $joinpoint->getObject();
		if ($mysqli->errno && isset($mysqli->context_class)) {
			$query = $joinpoint->getArguments()[0];
			$retry = true;
			switch ($mysqli->errno) {
				case Mysql_Errors::ER_NO_SUCH_TABLE:
					self::addTable($mysqli, $mysqli->context_class);
					break;
				case Mysql_Errors::ER_BAD_FIELD_ERROR:
					self::updateTable($mysqli, $mysqli->context_class);
					break;
				default:
					echo "erreur " . $mysqli->errno . "<br>";
					$retry = false;
					break;
			}
			if ($retry) {
				$result = $mysqli->query($query);
				$joinpoint->setReturnedValue($result);
			}
		}
	}

	//---------------------------------------------------------------------------- parseNameFromError
	private static function parseNameFromError($error)
	{
		$i = strpos($error, "'") + 1;
		$j = strpos($error, "'", $i);
		$name = substr($error, $i, $j - $i);
		if (strpos($name, ".")) {
			$name = substr($name, strrpos($name, ".") + 1);
		}
		if (substr($name, 0, 1) == "`" && substr($name, -1) == "`") {
			$name = substr($name, 1, -1);
		}
		return $name;
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after", "mysqli->query()", array(__CLASS__, "onMysqliQuery"));
	}

	//----------------------------------------------------------------------------------- updateTable
	private static function updateTable($mysqli, $class_name)
	{
		echo "mysqli update for $class_name<br>";
		$class_table = Mysql_Table_Builder_Class::build($class_name);
		$mysql_table = Mysql_Table_Builder_Mysqli::build($mysqli, Dao::storeNameOf($class_name));
		$mysql_columns = $mysql_table->getColumns();
		$builder = new Sql_Alter_Table_Builder();
		foreach ($class_table->getColumns() as $column) {
			if (!$mysql_columns[$column->getName()]) {
				$builder->addColumn($column);
			}
			elseif (!$column->equiv($mysql_columns[$column->getName()])) {
				$builder->alterColumn($column->getName(), $column);
			}
		}
		echo "<pre>update builder = " . print_r($builder, true) . "</pre>";
	}

}
