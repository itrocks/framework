<?php
namespace SAF\Framework;
use AopJoinpoint;
use mysqli;

class Mysql_Maintainer
{

	//----------------------------------------------------------------------------------- createTable
	private static function createTable($mysqli, $class_name)
	{
		$class_table = Mysql_Table_Builder_Class::build($class_name);
		$mysqli->query((new Sql_Create_Table_Builder($class_table))->build());
	}

	//--------------------------------------------------------------------------------- onMysqliQuery
	public static function onMysqliQuery(AopJoinpoint $joinpoint)
	{
		$mysqli = $joinpoint->getObject();
		$errno = $mysqli->errno;
		if ($errno && isset($mysqli->context)) {
			$error = $mysqli->error;
			$retry = false;
			$query = $joinpoint->getArguments()[0];
			$context = is_array($mysqli->context) ? $mysqli->context : array($mysqli->context);
			foreach ($context as $context_class) {
				switch ($errno) {
					case Mysql_Errors::ER_NO_SUCH_TABLE:
						if (Dao::storeNameOf($context_class) === self::parseNameFromError($error)) {
							self::createTable($mysqli, $context_class);
							$retry = true;
						}
						break;
					case Mysql_Errors::ER_BAD_FIELD_ERROR:
						self::updateTable($mysqli, $context_class);
						$retry = true;
						break;
				}
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
		$class_table = Mysql_Table_Builder_Class::build($class_name);
		$mysql_table = Mysql_Table_Builder_Mysqli::build($mysqli, Dao::storeNameOf($class_name));
		$mysql_columns = $mysql_table->getColumns();
		$builder = new Sql_Alter_Table_Builder($mysql_table);
		foreach ($class_table->getColumns() as $column) {
			if (!isset($mysql_columns[$column->getName()])) {
				$builder->addColumn($column);
			}
			elseif (!$column->equiv($mysql_columns[$column->getName()])) {
				$builder->alterColumn($column->getName(), $column);
			}
		}
		if ($builder->isReady()) {
			$mysqli->query($builder->build());
		}
	}

}
