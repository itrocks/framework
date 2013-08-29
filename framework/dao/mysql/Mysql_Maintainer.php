<?php
namespace SAF\Framework;

use AopJoinpoint;
use mysqli;

/**
 * This is an intelligent database maintainer that automatically updates a table structure if there
 * is an error when executing a query.
 */
class Mysql_Maintainer implements Plugin
{

	//----------------------------------------------------------------------------------- createTable
	/**
	 * Create a table in database, using a data class structure
	 *
	 * @param $mysqli     mysqli
	 * @param $class_name string
	 */
	private static function createTable(mysqli $mysqli, $class_name)
	{
		foreach ((new Mysql_Table_Builder_Class)->build($class_name) as $table) {
			$query = (new Sql_Create_Table_Builder($table))->build();
			$mysqli->query($query);
		}
	}

	//---------------------------------------------------------------------------- createImplicitType
	/**
	 * Create a table in database, which has no associated class, using fields names
	 *
	 * @param $mysqli       mysqli
	 * @param $table_name   string
	 * @param $column_names string[]
	 * @return boolean
	 */
	private static function createImplicitTable(mysqli $mysqli, $table_name, $column_names)
	{
		$table = new Mysql_Table($table_name);
		foreach ($column_names as $column_name) {
			$table->addColumn(
				($column_name === "id")
				? Mysql_Column_Builder::buildId()
				: Mysql_Column_Builder::buildLink($column_name)
			);
			if (substr($column_name, 0, 3) === "id_") {
				$index = Mysql_Index_Builder::buildLink($column_name);
				$table->addIndex($index);
			}
		}
		$mysqli->query((new Sql_Create_Table_Builder($table))->build());
		return true;
	}

	//--------------------------------------------------------------------- createTableWithoutContext
	/**
	 * Create table (probably links table) without context
	 *
	 * @param $mysqli     mysqli
	 * @param $table_name string
	 * @param $query      string
	 * @return boolean
	 */
	private static function createTableWithoutContext(mysqli $mysqli, $table_name, $query)
	{
		// if a class name exists for the table name, use it as context and create table from class
		$class_name = Dao::classNameOf($table_name);
		if (class_exists($class_name)) {
			self::createTable($mysqli, $class_name);
			return true;
		}
		// if no class name, create it from columns names in the query
		/** @noinspection PhpWrongStringConcatenationInspection */
		$alias = "t" . (
			(substr($query, strpos($query, "`" . $table_name . "` t") + strlen($table_name) + 4)) + 0
		);
		$i = 0;
		$column_names = array();
		while (($i = strpos($query, $alias . ".", $i)) !== false) {
			$i += strlen($alias) + 1;
			$field_name = substr($query, $i, strpos($query, " ", $i) - $i);
			$column_names[$field_name] = $field_name;
		}
		if (!$column_names) {
			if (substr($query, 0, 7) == "DELETE ") {
				// @todo create table without context DELETE columns detection
				trigger_error(
					"TODO Mysql maintainer create table $table_name from a DELETE query without context",
					E_USER_ERROR
				);
			}
			elseif (substr($query, 0, 12) == "INSERT INTO ") {
				$column_names = explode(",", str_replace(array("`", " "), "", mParse($query, "(", ")")));
			}
			elseif (substr($query, 0, 7) == "SELECT ") {
				// @todo create table without context SELECT columns detection (needs complete sql analyst)
				trigger_error(
					"TODO Mysql maintainer create table $table_name from a SELECT query without context",
					E_USER_ERROR
				);
			}
			elseif (substr($query, 0, 9) == "TRUNCATE ") {
				trigger_error(
					"Mysql maintainer can't create table $table_name from a TRUNCATE query without context",
					E_USER_ERROR
				);
				return false;
			}
			elseif (substr($query, 0, 7) == "UPDATE ") {
				// @todo create table without context UPDATE columns detection
				trigger_error(
					"TODO Mysql maintainer create table $table_name from a UPDATE query without context",
					E_USER_ERROR
				);
			}
		}
		return self::createImplicitTable($mysqli, $table_name, $column_names);
	}

	//--------------------------------------------------------------------------------- onMysqliQuery
	/**
	 * This is called after each mysql query in order to update automatically database structure in case of errors
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function onMysqliQuery(AopJoinpoint $joinpoint)
	{
		/** @var $mysqli Contextual_Mysqli */
		$mysqli = $joinpoint->getObject();
		$errno = $mysqli->errno;
		if ($errno && isset($mysqli->context)) {
			$query = $joinpoint->getArguments()[0];
			if (substr($query, 0, 9) !== "TRUNCATE ") {
				$error = $mysqli->error;
				//echo "$errno $error on $query context " . print_r($mysqli->context, true) . "<br>";
				$retry = false;
				$context = is_array($mysqli->context) ? $mysqli->context : array($mysqli->context);
				if (($errno == Mysql_Errors::ER_CANT_CREATE_TABLE) && strpos($error, "(errno: 150)")) {
					$error_table_names = self::parseNamesFromQuery($query);
					foreach ($error_table_names as $error_table_name) {
						if (!$mysqli->exists($error_table_name)) {
							self::createImplicitTable($mysqli, $error_table_name, array("id"));
						}
						$retry = true;
					}
				}
				elseif ($errno == Mysql_Errors::ER_NO_SUCH_TABLE) {
					$error_table_names = array(self::parseNameFromError($error));
					if (!reset($error_table_names)) {
						$error_table_names = self::parseNamesFromQuery($query);
					}
					foreach ($context as $key => $context_class) {
						$context_table = is_array($context_class) ? $key : Dao::storeNameOf($context_class);
						if (in_array($context_table, $error_table_names)) {
							if (!is_array($context_class)) {
								self::createTable($mysqli, $context_class);
							}
							else {
								self::createImplicitTable($mysqli, $context_table, $context_class);
							}
							$retry = true;
						}
					}
					if (!$retry) {
						foreach ($error_table_names as $error_table_name) {
							$retry = $retry || self::createTableWithoutContext(
								$mysqli, $error_table_name, $query
							);
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
	}

	//---------------------------------------------------------------------------- parseNameFromError
	/**
	 * Gets the first name between '' from a mysqli error message
	 *
	 * ie table name or field name
	 *
	 * @param $error string
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

	//--------------------------------------------------------------------------- parseNamesFromQuery
	/**
	 * Parse an SQL query to get all table names
	 *
	 * @param $query
	 * @return string[]
	 */
	private static function parseNamesFromQuery($query)
	{
		$tables = array();
		$i = 0;
		while (($i = strpos($query, "REFERENCES ", $i)) !== false) {
			$i = strpos($query, "`", $i) + 1;
			$j = strpos($query, "`", $i);
			$table_name = substr($query, $i, $j - $i);
			$tables[substr($query, $i, $j - $i)] = $table_name;
			$i = $j + 1;
		}
		return $tables;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers the Mysql maintainer plugin
	 */
	public static function register()
	{
		Aop::add(Aop::AFTER, "mysqli->query()", array(__CLASS__, "onMysqliQuery"));
	}

	//----------------------------------------------------------------------------------- updateTable
	/**
	 * Update table structure corresponding to a data class
	 *
	 * @param $mysqli mysqli
	 * @param $class_name string
	 * @return boolean true if an update query has been generated and executed
	 */
	private static function updateTable(mysqli $mysqli, $class_name)
	{
		$result = false;
		foreach ((new Mysql_Table_Builder_Class)->build($class_name) as $class_table) {;
			$mysql_table = Mysql_Table_Builder_Mysqli::build($mysqli, $class_table->getName());
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
				$result = true;
			}
		}
		return $result;
	}

}
