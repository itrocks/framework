<?php
namespace SAF\Framework\Dao\Mysql;

use mysqli;
use mysqli_result;
use SAF\Framework\Dao;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Sql\Builder\Alter_Table;
use SAF\Framework\Sql\Builder\Create_Table;
use SAF\Framework\Tools\Contextual_Mysqli;
use SAF\Framework\Tools\Names;
use SAF\Framework\Tools\Namespaces;

/**
 * This is an intelligent database maintainer that automatically updates a table structure if there
 * is an error when executing a query.
 */
class Maintainer implements Registerable
{

	//-------------------------------------------------------------------------------------- $already
	/**
	 * All queries that have been already solved by the maintainer
	 * Used to avoid trying to solve the same query twice (real errors)
	 *
	 * @var integer[] key is the query, value is the solved counter
	 */
	private $already = [];

	//----------------------------------------------------------------------------------- createTable
	/**
	 * Create a table in database, using a data class structure
	 *
	 * @param $mysqli     Contextual_Mysqli
	 * @param $class_name string
	 */
	private function createTable(Contextual_Mysqli $mysqli, $class_name)
	{
		$builder = new Table_Builder_Class();
		foreach ($builder->build($class_name) as $table) {
			$last_context = $mysqli->context;
			$mysqli->context = $builder->dependencies_context;
			$mysqli->query((new Create_Table($table))->build());
			$mysqli->context = $last_context;
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
	 * @todo mysqli context should contain sql builder (ie Select) in order to know if this was
	 *       an implicit link table. If then : only one unique index should be built
	 */
	private function createImplicitTable(mysqli $mysqli, $table_name, $column_names)
	{
		$only_ids = true;
		$table = new Table($table_name);
		$ids_index = new Index();
		$ids_index->setType(Index::UNIQUE);
		$indexes = [];
		foreach ($column_names as $column_name) {
			$table->addColumn(
				($column_name === 'id')
				? Column::buildId()
				: Column::buildLink($column_name)
			);
			if (substr($column_name, 0, 3) === 'id_') {
				if (($mysqli instanceof Contextual_Mysqli) && is_array($mysqli->context)) {
					$ids_index->addKey($column_name);
					$index = Index::buildLink($column_name);
					foreach ($mysqli->context as $context_class) {
						$id_context_property = 'id_' . Names::classToProperty(
							Names::setToSingle(Dao::storeNameOf($context_class))
						);
						$id_context_property_2 = 'id_' . Names::classToProperty(
							Names::setToSingle(Namespaces::shortClassName($context_class))
						);
						if (in_array($column_name, [$id_context_property, $id_context_property_2])) {
							$class = new Reflection_Class($context_class);
							if ($class->isAbstract()) {
								$class_column_name = substr($column_name, 3) . '_class';
								$column = new Column($class_column_name, 'varchar(255)');
								$table->addColumn($column);
								$index->addKey($class_column_name);
								$ids_index->addKey($class_column_name);
							}
							else {
								$table->addForeignKey(
									Foreign_Key::buildLink($table_name, $column_name, $context_class)
								);
							}
							break;
						}
					}
					$indexes[] = $index;
				}
			}
			else {
				$only_ids = false;
			}
		}
		if ($only_ids) {
			$table->addIndex($ids_index);
		}
		else {
			foreach ($indexes as $index) {
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
	private function createTableWithoutContext(Contextual_Mysqli $mysqli, $table_name, $query)
	{
		$query = str_replace(LF, SP, $query);
		// if a class name exists for the table name, use it as context and create table from class
		$class_name = Dao::classNameOf($table_name);
		if (class_exists($class_name, false)) {
			$this->createTable($mysqli, $class_name);
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
		return $this->createImplicitTable($mysqli, $table_name, $column_names);
	}

	//---------------------------------------------------------------------------------- guessContext
	/**
	 * @param $query string
	 * @return string[]|null
	 */
	private function guessContext($query)
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

	//------------------------------------------------------------------------ onCantCreateTableError
	/**
	 * @param $mysqli Contextual_Mysqli
	 * @param $query  string
	 * @return boolean true if the query with an error can be retried after this error was dealt with
	 */
	private function onCantCreateTableError(Contextual_Mysqli $mysqli, $query)
	{
		$retry = false;
		$error_table_names = $this->parseNamesFromQuery($query);
		foreach ($error_table_names as $error_table_name) {
			if (!$mysqli->exists($error_table_name)) {
				$this->createImplicitTable($mysqli, $error_table_name, ['id']);
			}
			$retry = true;
		}
		return $retry;
	}

	//--------------------------------------------------------------------------------- onMysqliQuery
	/**
	 * This is called after each mysql query in order to update automatically database structure in case of errors
	 *
	 * @param $object Contextual_Mysqli
	 * @param $query  string
	 * @param $result mysqli_result|boolean
	 */
	public function onMysqliQuery(Contextual_Mysqli $object, $query, &$result)
	{
		$mysqli = $object;
		if ($mysqli->last_errno && !isset($this->already[$query])) {
			$this->already[$query] = 1;
			if (!isset($mysqli->context)) {
				$mysqli->context = $this->guessContext($query);
			}
			if (isset($mysqli->context)) {
				$retry = false;
				$context = is_array($mysqli->context) ? $mysqli->context : [$mysqli->context];
				if (
					($mysqli->last_errno == Errors::ER_CANT_CREATE_TABLE)
					&& strpos($mysqli->last_error, '(errno: 150)')
				) {
					$retry = $this->onCantCreateTableError($mysqli, $query);
				}
				elseif ($mysqli->last_errno == Errors::ER_NO_SUCH_TABLE) {
					$retry = $this->onNoSuchTableError($mysqli, $query, $context);
				}
				elseif (
					in_array($mysqli->last_errno, [Errors::ER_BAD_FIELD_ERROR, Errors::ER_CANNOT_ADD_FOREIGN])
				) {
					$retry = $this->updateContextTables($mysqli, $context);
				}
				if ($retry) {
					$result = $mysqli->query($query);
				}
			}
		}
	}

	//---------------------------------------------------------------------------- onNoSuchTableError
	/**
	 * @param $mysqli  Contextual_Mysqli
	 * @param $query   string
	 * @param $context string[]
	 * @return boolean true if the query with an error can be retried after this error was dealt with
	 */
	private function onNoSuchTableError(Contextual_Mysqli $mysqli, $query, $context)
	{
		$retry = false;
		$error_table_names = [$this->parseNameFromError($mysqli->last_error)];
		if (!reset($error_table_names)) {
			$error_table_names = $this->parseNamesFromQuery($query);
		}
		foreach ($context as $key => $context_class) {
			$context_table = is_array($context_class) ? $key : Dao::storeNameOf($context_class);
			if (in_array($context_table, $error_table_names)) {
				if (!is_array($context_class)) {
					$this->createTable($mysqli, $context_class);
				}
				else {
					$this->createImplicitTable($mysqli, $context_table, $context_class);
				}
				$retry = true;
			}
		}
		if (!$retry) {
			foreach ($error_table_names as $error_table_name) {
				$retry = $retry
					|| $this->createTableWithoutContext(
						$mysqli, $error_table_name, $query
					);
			}
			return $retry;
		}
		return $retry;
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
	private function parseNameFromError($error)
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
	private function parseNamesFromQuery($query)
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

	//--------------------------------------------------------------------------- updateContextTables
	/**
	 * @param $mysqli  Contextual_Mysqli
	 * @param $context string[]
	 * @return boolean true if the query with an error can be retried after this error was dealt with
	 */
	private function updateContextTables($mysqli, $context)
	{
		foreach ($context as $context_class) {
			if (self::updateTable($mysqli, $context_class)) {
				$retry = true;
			}
		}
		return isset($retry);
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
		foreach ((new Table_Builder_Class)->build($class_name) as $class_table) {
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
