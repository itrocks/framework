<?php
namespace SAF\Framework\Dao\Mysql;

use mysqli;
use mysqli_result;
use SAF\Framework\Dao;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Sql\Builder\Alter_Table;
use SAF\Framework\Sql\Builder\Create_Table;
use SAF\Framework\Tools\Contextual_Mysqli;
/**
 * This is an intelligent database maintainer that automatically updates a table structure if there
 * is an error when executing a query.
 */
class Maintainer implements Registerable
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
		foreach ((new Table_Builder_Class)->build($class_name) as $table) {
			$mysqli->query((new Create_Table($table))->build());
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
		$table = new Table($table_name);
		foreach ($column_names as $column_name) {
			$table->addColumn(
				($column_name === 'id')
				? Column::buildId()
				: Column::buildLink($column_name)
			);
			if (substr($column_name, 0, 3) === 'id_') {
				$index = Index::buildLink($column_name);
				$table->addIndex($index);
			}
		}
		$mysqli->query((new Create_Table($table))->build());
		return true;
	}

	//--------------------------------------------------------------------- createTableWithoutContext
	/**
	 * Create table (probably links table) without context
	 *
	 * @param $mysqli     Contextual_Mysqli
	 * @param $table_name string
	 * @param $query      string
	 * @return boolean
	 */
	private static function createTableWithoutContext(Contextual_Mysqli $mysqli, $table_name, $query)
	{
		// if a class name exists for the table name, use it as context and create table from class
		$class_name = Dao::classNameOf($table_name);
		if (class_exists($class_name, false)) {
			self::createTable($mysqli, $class_name);
			return true;
		}
		// if no class name, create it from columns names in the query
		/** @noinspection PhpWrongStringConcatenationInspection */
		$alias = 't' . (
			(substr($query, strpos($query, BQ . $table_name . BQ . SP . 't') + strlen($table_name) + 4)) + 0
		);
		$i = 0;
		$column_names = [];
		while (($i = strpos($query, $alias . DOT, $i)) !== false) {
			$i += strlen($alias) + 1;
			$field_name = substr($query, $i, strpos($query, SP, $i) - $i);
			$column_names[$field_name] = $field_name;
		}
		if (!$column_names) {
			if ($mysqli->isDelete($query)) {
				// @todo create table without context DELETE columns detection
				trigger_error(
					"TODO Mysql maintainer create table $table_name from a DELETE query without context",
					E_USER_ERROR
				);
			}
			elseif ($mysqli->isInsert($query)) {
				$column_names = explode(',', str_replace([BQ, SP], '', mParse($query, '(', ')')));
			}
			elseif ($mysqli->isSelect($query)) {
				// @todo create table without context SELECT columns detection (needs complete sql analyst)
				trigger_error(
					"TODO Mysql maintainer create table $table_name from a SELECT query without context",
					E_USER_ERROR
				);
			}
			elseif ($mysqli->isTruncate($query)) {
				trigger_error(
					"Mysql maintainer can't create table $table_name from a TRUNCATE query without context",
					E_USER_ERROR
				);
				return false;
			}
			elseif ($mysqli->isUpdate($query)) {
				// @todo create table without context UPDATE columns detection
				trigger_error(
					"TODO Mysql maintainer create table $table_name from a UPDATE query without context",
					E_USER_ERROR
				);
			}
		}
		return self::createImplicitTable($mysqli, $table_name, $column_names);
	}

	//---------------------------------------------------------------------------------- guessContext
	/**
	 * @param $query string
	 * @return string[]|null
	 */
	private static function guessContext($query)
	{
		$context = [];
		// first clause between `...` is probably the name of the table
		$table_name = mParse($query, BQ, BQ);
		if ($table_name) {
			$class_name = Dao::classNameOf($table_name);
			if ($class_name) {
				$context[] = $class_name;
			}
		}
		// every JOIN `...` may be the name of a table
		$joins = explode('JOIN ' . BQ, $query);
		array_shift($joins);
		foreach ($joins as $join) {
			$table_name = lParse($join, BQ);
			if ($table_name) {
				$class_name = Dao::classNameOf($table_name);
				if ($class_name) {
					$context[] = $class_name;
				}
			}
		}
		return $context ? $context : null;
	}

	//--------------------------------------------------------------------------------- onMysqliQuery
	/**
	 * This is called after each mysql query in order to update automatically database structure in case of errors
	 *
	 * @param $object Contextual_Mysqli
	 * @param $query  string
	 * @param $result mysqli_result|boolean
	 */
	public static function onMysqliQuery(Contextual_Mysqli $object, $query, &$result)
	{
		$mysqli = $object;
		if ($error_number = $mysqli->last_errno) {
			if (!isset($mysqli->context)) {
				$mysqli->context = self::guessContext($query);
			}
			if (isset($mysqli->context)) {
				$error = $mysqli->error;
				$retry = false;
				$context = is_array($mysqli->context) ? $mysqli->context : [$mysqli->context];
				if (
					($error_number == Errors::ER_CANT_CREATE_TABLE)
					&& strpos($error, '(errno: 150)')
				) {
					$error_table_names = self::parseNamesFromQuery($query);
					foreach ($error_table_names as $error_table_name) {
						if (!$mysqli->exists($error_table_name)) {
							self::createImplicitTable($mysqli, $error_table_name, ['id']);
						}
						$retry = true;
					}
				}
				elseif ($error_number == Errors::ER_NO_SUCH_TABLE) {
					$error_table_names = [self::parseNameFromError($error)];
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
				elseif ($error_number == Errors::ER_BAD_FIELD_ERROR) {
					foreach ($context as $context_class) {
						if (self::updateTable($mysqli, $context_class)) {
							$retry = true;
						}
					}
				}
				if ($retry) {
					$result = $mysqli->query($query);
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
		$i = strpos($error, Q) + 1;
		$j = strpos($error, Q, $i);
		$name = substr($error, $i, $j - $i);
		if (strpos($name, DOT)) {
			$name = substr($name, strrpos($name, DOT) + 1);
		}
		if ((substr($name, 0, 1) == BQ) && (substr($name, -1) == BQ)) {
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
		$tables = [];
		$i = 0;
		while (($i = strpos($query, 'REFERENCES ', $i)) !== false) {
			$i = strpos($query, BQ, $i) + 1;
			$j = strpos($query, BQ, $i);
			$table_name = substr($query, $i, $j - $i);
			$tables[substr($query, $i, $j - $i)] = $table_name;
			$i = $j + 1;
		}
		return $tables;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers the Mysql maintainer plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod([Contextual_Mysqli::class, 'query'], [$this, 'onMysqliQuery']);
	}

	//----------------------------------------------------------------------------------- updateTable
	/**
	 * Update table structure corresponding to a data class
	 *
	 * @param $mysqli mysqli
	 * @param $class_name string
	 * @return boolean true if an update query has been generated and executed
	 */
	public static function updateTable(mysqli $mysqli, $class_name)
	{
		$result = false;
		foreach ((new Table_Builder_Class)->build($class_name) as $class_table) {;
			$mysql_table = Table_Builder_Mysqli::build($mysqli, $class_table->getName());
			if (!$mysql_table) {
				$mysqli->query((new Create_Table($class_table))->build());
				$result = true;
			}
			else {
				$mysql_columns = $mysql_table->getColumns();
				$builder = new Alter_Table($mysql_table);
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
		}
		return $result;
	}

}
