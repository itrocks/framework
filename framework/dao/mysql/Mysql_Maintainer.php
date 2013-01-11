<?php
namespace SAF\Framework;
use AopJoinpoint;
use mysqli;

class Mysql_Maintainer implements Plugin
{

	//----------------------------------------------------------------------------------- createTable
	/**
	 * Create a table in database, using a data class structure
	 *
	 * @param mysqli $mysqli
	 * @param string $class_name
	 */
	private static function createTable(mysqli $mysqli, $class_name)
	{
		$table = Mysql_Table_Builder_Class::build($class_name);
		$mysqli->query((new Sql_Create_Table_Builder($table))->build());
	}

	//---------------------------------------------------------------------------- createImplicitType
	/**
	 * Create a table in database, which has no associated class, using fields names
	 *
	 * @param mysqli $mysqli
	 * @param unknown_type $columns
	 */
	private static function createImplicitTable($mysqli, $table_name, $column_names)
	{
		$table = new Mysql_Table($table_name);
		$table->addColumn(Mysql_Column_Builder_Property::buildId());
		foreach ($column_names as $column_name) {
			$table->addColumn(Mysql_Column_Builder_Property::buildLink($column_name));
		}
		$create_builder = new Sql_Create_Table_Builder($table);
		$mysqli->query($create_builder->build());
	}

	//--------------------------------------------------------------------------------- onMysqliQuery
	/**
	 * This is called after each mysql query in order to update automatically database structure in case of errors
	 *
	 * @param AopJoinpoint $joinpoint
	 */
	public static function onMysqliQuery(AopJoinpoint $joinpoint)
	{
		$mysqli = $joinpoint->getObject();
		$errno = $mysqli->errno;
		if ($errno && isset($mysqli->context)) {
			$error = $mysqli->error;
			$retry = false;
			$query = $joinpoint->getArguments()[0];
			$context = is_array($mysqli->context) ? $mysqli->context : array($mysqli->context);
			if ($errno == Mysql_Errors::ER_NO_SUCH_TABLE) {
				$error_table_name = self::parseNameFromError($error);
				foreach ($context as $key => $context_class) {
					$context_table = is_array($context_class) ? $key : Dao::storeNameOf($context_class);
					if ($context_table === $error_table_name) {
						if (!is_array($context_class)) {
							self::createTable($mysqli, $context_class);
						}
						else {
							self::createImplicitTable($mysqli, $context_table, $context_class);
						}
						$retry = true;
					}
				}
			}
			elseif ($errno == Mysql_Errors::ER_BAD_FIELD_ERROR) {
				foreach ($context as $context_class) {
					if (self::updateTable($mysqli, $context_class)) {
						$retry = true;
					}
				}
			}
			if ($retry) {
				$result = $mysqli->query($query);
				$joinpoint->setReturnedValue($result);
			}
		}
	}

	//---------------------------------------------------------------------------- parseNameFromError
	/**
	 * Gets the first name between '' from a mysqli error message
	 *
	 * ie table name or field name
	 *
	 * @param string $error
	 * @return string
	 */
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
	/**
	 * Update table structure corresponding to a data class
	 *
	 * @param mysqli $mysqli
	 * @param string $class_name
	 * @return boolean true if an update query has been generated and executed
	 */
	private static function updateTable(mysqli $mysqli, $class_name)
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
			return true;
		}
		return false;
	}

}
