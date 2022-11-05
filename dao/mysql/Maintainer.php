<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\AOP\Joinpoint\Before_Method;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Class_\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Sql\Builder\Alter_Table;
use ITRocks\Framework\Sql\Builder\Create_Table;
use ITRocks\Framework\Sql\Builder\Create_View;
use ITRocks\Framework\Sql\Link_Table;
use ITRocks\Framework\Tools\Call_Stack;
use ITRocks\Framework\Tools\Contextual_Mysqli;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Namespaces;
use mysqli_result;

/**
 * This is an intelligent database maintainer that automatically updates a table structure if there
 * is an error when executing a query.
 */
class Maintainer implements Configurable, Registerable
{
	use Has_Get;

	//------------------------------------------------------------------------------- EXCLUDE_CLASSES
	/**
	 * Configuration key for list of excluded (not maintained because plugin is off) classes
	 */
	const EXCLUDE_CLASSES = 'exclude_classes';

	//---------------------------------------------------------------------------------------- OUTPUT
	/**
	 * value for $notice : output what is done by the maintainer
	 */
	const OUTPUT  = 'output';

	//--------------------------------------------------------------------------------------- VERBOSE
	/**
	 * value for $notice : verbose output (more things are told)
	 */
	const VERBOSE = 'verbose';

	//--------------------------------------------------------------------------------------- WARNING
	/**
	 * value for $notice : trigger a warning for each query done by the maintainer (default)
	 */
	const WARNING = 'warning';

	//------------------------------------------------------------------------------------ $MAX_RETRY
	/**
	 * Maximum retries count
	 * Mandatory for errors codes using $retry
	 *
	 * This is a private static instead of a const because isset / defined does not work with const
	 *
	 * Notice : or a MAX_RETRY of 5, the query will be executed until 6 times :
	 * the 1st try, added to the 5 retries, equals 6.
	 *
	 * @var integer[]
	 */
	private static array $MAX_RETRY = [
		Errors::ER_DUP_ENTRY     => 5,
		Errors::ER_NO_SUCH_TABLE => 2
	];

	//-------------------------------------------------------------------------------------- $already
	/**
	 * All queries that have been already solved by the maintainer
	 * Used to avoid trying to solve the same query twice when they recurse (real errors)
	 *
	 * Some errors codes allow multiple retries, setup into MAX_RETRY
	 *
	 * @var array integer[string $query][integer $last_error] value is the solved / retries counter
	 */
	private array $already = [];

	//-------------------------------------------------------------------------- $create_empty_tables
	/**
	 * Set this to true if you want empty implicit tables to be created even if it will be empty
	 *
	 * @var boolean
	 */
	public bool $create_empty_tables = false;

	//------------------------------------------------------------------------------ $exclude_classes
	/**
	 * @var string[] Class names
	 */
	public array $exclude_classes = [];

	//--------------------------------------------------------------------------------------- $notice
	/**
	 * If true notice column differences using error notice
	 *
	 * @values self::const local
	 * @var string
	 */
	public string $notice = '';

	//------------------------------------------------------------------------------------- $requests
	/**
	 * SQL Requests log during simulation mode
	 *
	 * @var string[]
	 */
	public array $requests = [];

	//----------------------------------------------------------------------------------- $simulation
	/**
	 * If true Maintainer is in simulation mode
	 *
	 * @var boolean
	 */
	private bool $simulation = false;

	//-------------------------------------------------------------------------------------- $verbose
	/**
	 * @var boolean
	 */
	public bool $verbose = false;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct($configuration = [])
	{
		foreach ($configuration as $property_name => $property_value) {
			$this->$property_name = $property_value;
		}
	}

	//--------------------------------------------------------------------------- createImplicitTable
	/**
	 * Create a table in database, which has no associated class, using fields names
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $mysqli       Contextual_Mysqli
	 * @param $table_name   string
	 * @param $column_names string[]
	 * @return boolean
	 * @todo mysqli context should contain sql builder (ie Select) in order to know if this was
	 *       an implicit link table. If then : only one unique index should be built
	 */
	private function createImplicitTable(
		Contextual_Mysqli $mysqli, string $table_name, array $column_names
	) : bool
	{
		$only_ids  = true;
		$table     = new Table($table_name);
		$ids_index = new Index();
		$ids_index->setType(Index::PRIMARY);
		$indexes = [];
		foreach ($column_names as $column_name) {
			$table->addColumn(
				($column_name === 'id')
					? Column::buildId()
					: Column::buildLink($column_name)
			);
			if (str_starts_with($column_name, 'id_')) {
				if (($mysqli instanceof Contextual_Mysqli) && is_array($context = end($mysqli->contexts))) {
					$ids_index->addKey($column_name);
					$index = Index::buildLink($column_name);
					foreach ($context as $context_class) {
						if (is_string($context_class) && class_exists($context_class)) {
							/** @noinspection PhpUnhandledExceptionInspection class_exists */
							$context_reflection_class = new Reflection_Class($context_class);
							if ($maintain = $context_reflection_class->getAnnotation('maintain')->value) {
								$context_class = $maintain;
							}
						}
						if (!is_string($context_class)) {
							continue;
						}
						$id_context_property = 'id_' . Names::classToProperty(
							Names::setToSingle(Dao::storeNameOf($context_class))
						);
						$id_context_property_2 = 'id_' . Names::classToProperty(
							Names::setToSingle(Namespaces::shortClassName($context_class))
						);
						if (in_array($column_name, [$id_context_property, $id_context_property_2], true)) {
							$table->addForeignKey(Foreign_Key::buildLink(
								$table_name, $column_name, $context_class
							));
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
		foreach ((new Create_Table($table))->build() as $query) {
			$this->query($mysqli, $query);
		}
		return true;
	}

	//----------------------------------------------------------------------------------- createTable
	/**
	 * Create a table in database, using a data class structure
	 *
	 * @param $class_name string
	 * @param $mysqli     Contextual_Mysqli|null
	 */
	private function createTable(string $class_name, Contextual_Mysqli $mysqli = null) : void
	{
		if (!$mysqli) {
			$data_link = Dao::current();
			if ($data_link instanceof Link) {
				$mysqli = $data_link->getConnection();
			}
			else {
				trigger_error('Must call createTable() with a valid $mysqli link', E_USER_ERROR);
			}
		}
		$builder = new Table_Builder_Class();
		$build   = $builder->build($class_name);
		foreach ($build as $table) {
			if (!$mysqli->exists($table->getName())) {
				$queries            = (new Create_Table($table))->build();
				$mysqli->contexts[] = array_merge($builder->dependencies_context, [$class_name]);
				foreach ($queries as $query) {
					$this->updateContextAfterCreate($mysqli, $query, $class_name);
					$this->query($mysqli, $query);
				}
				array_pop($mysqli->contexts);
			}
		}
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
	private function createTableWithoutContext(
		Contextual_Mysqli $mysqli, string $table_name, string $query
	) : bool
	{
		$query = str_replace(LF, SP, $query);
		// if a class name exists for the table name, use it as context and create table from class
		$class_names = Dao::classNamesOf($table_name);
		foreach ($class_names as $class_name) {
			if (class_exists($class_name, false)) {
				if (isset($created)) {
					$this->updateTable($class_name, $mysqli);
				}
				else {
					$this->createTable($class_name, $mysqli);
				}
				$created = true;
			}
			if (isset($created)) {
				return true;
			}
		}
		// if no class name, create it from columns names in the query
		$column_names = [];
		$alias_pos    = 0;
		while (true) {
			$alias_pos = strpos($query, BQ . $table_name . BQ . SP . 't', $alias_pos);
			if (!$alias_pos) {
				break;
			}
			$alias_pos += strlen($table_name) + 4;
			$alias      = 't' . intval(substr($query, $alias_pos));
			$i          = 0;
			while (($i = strpos($query, $alias . DOT, $i)) !== false) {
				$i                        += strlen($alias) + 1;
				$j                         = strpos($query, SP, $i) ?: strlen($query);
				$field_name                = trim(substr($query, $i, $j - $i), BQ);
				$column_names[$field_name] = $field_name;
			}
		}
		if (!$column_names) {
			if ($mysqli->isDelete($query)) {
				// TODO create table without context DELETE columns detection
				trigger_error(
					"TODO Mysql maintainer create table $table_name from a DELETE query without context",
					E_USER_ERROR
				);
			}
			elseif ($mysqli->isInsert($query)) {
				if (str_contains($query, BQ . ' SET ')) {
					$column_names = explode(',', str_replace([BQ, SP], '', rParse($query, BQ . ' SET ')));
					foreach ($column_names as &$column_name) {
						$column_name = trim(lParse($column_name, '='));
					}
				}
				else {
					$column_names = explode(
						',', str_replace([BQ, SP], '', lLastParse(rParse($query, '('), ')', 2))
					);
				}
			}
			elseif ($mysqli->isSelect($query) || $mysqli->isExplainSelect($query)) {
				// TODO create table without context SELECT columns detection (needs complete sql analyst)
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
				/** @noinspection PhpUnreachableStatementInspection Error may be caught and ignored */
				return false;
			}
			elseif ($mysqli->isUpdate($query)) {
				// TODO create table without context UPDATE columns detection
				trigger_error(
					"TODO Mysql maintainer create table $table_name from a UPDATE query without context",
					E_USER_ERROR
				);
			}
		}
		return $this->createImplicitTable($mysqli, $table_name, $column_names);
	}

	//------------------------------------------------------------------------------------ createView
	/**
	 * Create a table in database, using a data class structure
	 *
	 * @param $class_name string
	 * @param $mysqli     Contextual_Mysqli|null
	 */
	private function createView(string $class_name, Contextual_Mysqli $mysqli = null) : void
	{
		if (!$mysqli) {
			$data_link = Dao::current();
			if ($data_link instanceof Link) {
				$mysqli = $data_link->getConnection();
			}
			else {
				trigger_error('Must call createTable() with a valid $mysqli link', E_USER_ERROR);
			}
		}
		$builder = new View_Builder_Class();
		$build   = $builder->build($class_name, $mysqli);
		foreach ($build as $view) {
			if (!$mysqli->exists($view->getName())) {
				$queries            = (new Create_View($view))->build();
				$mysqli->contexts[] = array_keys($view->select_queries);
				foreach ($queries as $query) {
					$this->query($mysqli, $query);
				}
				array_pop($mysqli->contexts);
			}
		}
	}

	//---------------------------------------------------------------------------------- guessContext
	/**
	 * @param $query  string
	 * @param $mysqli Contextual_Mysqli
	 * @return ?string[]
	 */
	private function guessContext(string $query, Contextual_Mysqli $mysqli) : ?array
	{
		$context = [];
		if ($mysqli->isSelect($query) || $mysqli->isExplainSelect($query)) {
			// SELECT : first clause between `...` after FROM is the name of the table
			$table_name = mParse($query, ['FROM', BQ], BQ);
		}
		else {
			// DELETE, INSERT, UPDATE
			// first clause between `...` is probably the name of the table
			$table_name = mParse($query, BQ, BQ);
		}
		if ($table_name) {
			foreach (Dao::classNamesOf($table_name) as $class_name) {
				if (str_contains($class_name, BS)) {
					$context[] = $class_name;
				}
			}
		}
		// every JOIN `...` may be the name of a table
		$joins = explode('JOIN ' . BQ, $query);
		array_shift($joins);
		foreach ($joins as $join) {
			$table_name = lParse($join, BQ);
			if ($table_name) {
				$class_names = Dao::classNamesOf($table_name);
				foreach ($class_names as $class_name) {
					if (str_contains($class_name, BS)) {
						$context[] = $class_name;
					}
				}
			}
		}
		return $context ?: null;
	}

	//------------------------------------------------------------------------ onCantCreateTableError
	/**
	 * @param $mysqli Contextual_Mysqli
	 * @param $query  string
	 * @return boolean true if the query with an error can be retried after this error was dealt with
	 */
	private function onCantCreateTableError(Contextual_Mysqli $mysqli, string $query) : bool
	{
		$retry             = false;
		$error_table_names = $this->parseNamesFromQuery($query);
		foreach ($error_table_names as $error_table_name) {
			if (!$mysqli->exists($error_table_name)) {
				$this->createImplicitTable($mysqli, $error_table_name, ['id']);
			}
			$retry = true;
		}
		return $retry;
	}

	//---------------------------------------------------------------------------- onMysqliQueryError
	/**
	 * This is called after each mysql query in order to update automatically database structure in
	 * case of errors
	 *
	 * @param $object    Contextual_Mysqli
	 * @param $query     string
	 * @param $joinpoint Before_Method
	 */
	public function onMysqliQueryError(
		Contextual_Mysqli $object, string $query, Before_Method $joinpoint
	) : void
	{
		$mysqli     = $object;
		$last_errno = $mysqli->last_errno;
		$last_error = $mysqli->last_error;
		$max_retry  = static::$MAX_RETRY[$last_errno] ?? 1;
		if (!isset($this->already[$query][$last_error])) {
			$this->already[$query][$last_error] = 0;
		}
		if ($last_errno && ($this->already[$query][$last_error] < $max_retry)) {
			$pop_context = false;
			$this->already[$query][$last_error] ++;
			if (!end($mysqli->contexts)) {
				$key                    = ($mysqli->contexts ? key($mysqli->contexts) : 0);
				$pop_context            = boolval($mysqli->contexts);
				$mysqli->contexts[$key] = $this->guessContext($query, $mysqli);
			}
			$retry = false;
			// errors solving that need a context
			if (end($mysqli->contexts)) {
				$mysqli->last_errno = $last_errno;
				$mysqli->last_error = $last_error;
				if (
					in_array($last_errno, [Errors::ER_BAD_FIELD_ERROR, Errors::ER_CANNOT_ADD_FOREIGN], true)
				) {
					$retry = $this->updateContextTables($mysqli);
				}
				elseif (
					($last_errno === Errors::ER_CANT_CREATE_TABLE) && str_contains($last_error, '(errno: 150)')
				) {
					$retry = $this->onCantCreateTableError($mysqli, $query);
				}
				elseif ($last_errno === Errors::ER_NO_SUCH_TABLE) {
					$retry = $this->onNoSuchTableError($mysqli, $query);
				}
			}
			// errors solving that do not need a context
			// ER_DUP_ENTRY : this is to patch a bug into MySQL 5.7
			if ($last_errno === Errors::ER_DUP_ENTRY) {
				$retry = true;
			}
			// retry
			if ($retry) {
				$joinpoint->result = $mysqli->query($query);
				if (!$mysqli->last_errno && !$mysqli->last_error) {
					$joinpoint->stop = true;
				}
			}
			// the error has not been cleaned : reset original errors codes
			if (!$joinpoint->stop) {
				$mysqli->last_errno = $last_errno;
				$mysqli->last_error = $last_error;
			}
			$this->already[$query][$last_error] --;
			if ($pop_context) {
				array_pop($mysqli->contexts);
			}
		}
		if (!$this->already[$query][$last_error]) {
			unset($this->already[$query][$last_error]);
		}
		if (!$this->already[$query]){
			unset($this->already[$query]);
		}
	}

	//---------------------------------------------------------------------------- onNoSuchTableError
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $mysqli Contextual_Mysqli
	 * @param $query  string
	 * @return boolean true if the query with an error can be retried after this error was dealt with
	 * @see Contextual_Mysqli::$contexts
	 */
	private function onNoSuchTableError(Contextual_Mysqli $mysqli, string $query) : bool
	{
		$context = end($mysqli->contexts);
		if (!is_array($context)) {
			$context = [$context];
		}
		$retry             = false;
		$error_table_names = [$this->parseNameFromError($mysqli->last_error)];
		if (!reset($error_table_names)) {
			$error_table_names = $this->parseNamesFromQuery($query);
		}
		foreach ($context as $key => $context_class) {
			if (is_string($context_class) && class_exists($context_class)) {
				/** @noinspection PhpUnhandledExceptionInspection class_exists */
				$context_reflection_class = new Reflection_Class($context_class);
				if ($maintain = $context_reflection_class->getAnnotation('maintain')->value) {
					$context_class = $maintain;
				}
			}
			$context_table = is_array($context_class) ? $key : Dao::storeNameOf($context_class);
			if (in_array($context_table, $error_table_names, true) || !$mysqli->exists($context_table)) {
				if (is_array($context_class)) {
					$this->createImplicitTable($mysqli, $context_table, $context_class);
				}
				elseif ((new Type($context_class))->isAbstractClass()) {
					$this->createView($context_class, $mysqli);
				}
				else {
					$this->createTable($context_class, $mysqli);
				}
				$retry = true;
			}
		}
		if (!$retry) {
			foreach ($error_table_names as $error_table_name) {
				$retry = $retry || $this->createTableWithoutContext($mysqli, $error_table_name, $query);
			}
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
	private function parseNameFromError(string $error) : string
	{
		$i    = strpos($error, Q) + 1;
		$j    = strpos($error, Q, $i);
		$name = substr($error, $i, $j - $i);
		if (str_contains($name, DOT)) {
			$name = substr($name, strrpos($name, DOT) + 1);
		}
		if (str_starts_with($name, BQ) && str_ends_with($name, BQ)) {
			$name = substr($name, 1, -1);
		}
		return $name;
	}

	//--------------------------------------------------------------------------- parseNamesFromQuery
	/**
	 * Parse an SQL query to get all table names
	 *
	 * @param $query string
	 * @return string[]
	 */
	private function parseNamesFromQuery(string $query) : array
	{
		$tables = [];
		$i      = 0;
		while (($i = strpos($query, 'REFERENCES ', $i)) !== false) {
			$i          = strpos($query, BQ, $i) + 1;
			$j          = strpos($query, BQ, $i);
			$table_name = substr($query, $i, $j - $i);

			$tables[substr($query, $i, $j - $i)] = $table_name;

			$i = $j + 1;
		}
		return $tables;
	}

	//----------------------------------------------------------------------------------------- query
	/**
	 * @param $mysqli Contextual_Mysqli
	 * @param $query  string
	 * @return boolean|mysqli_result
	 */
	public function query(Contextual_Mysqli $mysqli, string $query) : bool|mysqli_result
	{
		$this->requests[] = $query;
		if ($this->simulation) {
			switch ($this->notice) {
				case self::OUTPUT:
				case self::VERBOSE:
					echo 'Simulation : ' . $query . BRLF;
					break;
				case self::WARNING:
					trigger_error('Simulation : ' . $query);
			}
			return true;
		}
		if (strStartsWith($query, ['ALTER TABLE', 'CREATE TABLE'])) {
			foreach ($mysqli->getViews() as $view_name) {
				if (str_ends_with($view_name, '_view')) {
					$mysqli->queryWhenUnlocked('DROP VIEW' . SP . BQ . $view_name . BQ);
				}
			}
		}
		return $mysqli->query($query);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers the Mysql maintainer plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		$aop = $register->aop;
		$aop->beforeMethod([Contextual_Mysqli::class, 'queryError'], [$this, 'onMysqliQueryError']);
	}

	//----------------------------------------------------------------------------- removeEmptyTables
	/**
	 * Remove all empty tables than can be removed
	 * Retry removals until no table can be removed at all (capture constraint errors)
	 *
	 * @param $simulation boolean
	 */
	public function removeEmptyTables(bool $simulation = false)
	{
		$dropped = 1;
		/** @var $link Link */
		$link   = Dao::current();
		$mysqli = $link->getConnection();
		while ($dropped) {
			$dropped = 0;
			foreach ($mysqli->getTables() as $table_name) {
				/** @noinspection SqlResolve dynamic */
				if ($mysqli->query("SELECT COUNT(*) FROM `$table_name`")->fetch_row()[0]) {
					continue;
				}
				try {
					if (!$simulation) {
						$mysqli->drop($table_name);
					}
					if ($mysqli->last_errno) {
						echo "! Cannot remove empty table $table_name : $mysqli->last_error" . BRLF;
					}
					else {
						$dropped ++;
						if ($this->notice) {
							echo "- Removed table $table_name" . BRLF;
						}

					}
				}
				/** @noinspection PhpRedundantCatchClauseInspection throw was ignored, but exists */
				catch (Mysql_Error_Exception) {
					echo "! Cannot remove empty table $table_name : $mysqli->last_error" . BRLF;
				}
			}
		}
	}

	//------------------------------------------------------------------------------- simulationStart
	/**
	 * Starts maintainer simulation
	 */
	public function simulationStart() : void
	{
		$this->simulation = true;
		$this->requests   = [];
	}

	//-------------------------------------------------------------------------------- simulationStop
	/**
	 * Stops maintainer simulation
	 *
	 * @return string[]
	 */
	public function simulationStop() : array
	{
		$this->simulation = false;
		return $this->requests;
	}

	//---------------------------------------------------------------------- updateContextAfterCreate
	/**
	 * Call this before an ALTER TABLE CREATE FOREIGN to ensure that foreign tables are ok
	 *
	 * Verify foreign table constraints once the table is created (first query).
	 * This a patch to avoid crashes on this scenario :
	 * add constraint > error > create foreign table > add constraint => strange mysql error.
	 *
	 * After calling this, you can alter table (or anything else if it was not an ALTER TABLE)
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $mysqli     Contextual_Mysqli the connexion to the contextual mysqli used for queries
	 * @param $query      string the query that is going to be executed (to filter by 'ALTER TABLE')
	 * @param $class_name string the name of the class to exclude from updates (main query class)
	 */
	private function updateContextAfterCreate(
		Contextual_Mysqli $mysqli, string $query, string $class_name
	) : void
	{
		if (!$query || str_starts_with(trim($query), 'ALTER TABLE')) {
			if ($context = end($mysqli->contexts)) foreach ($context as $context_class_name) {
				$same_table = false;
				if ($context_class_name === $class_name) {
					$same_table = true;
				}
				elseif (class_exists($context_class_name) && class_exists($class_name)) {
					/** @noinspection PhpUnhandledExceptionInspection known class names */
					$same_table = (
						Store_Name_Annotation::of(new Reflection_Class($context_class_name))->value
						=== Store_Name_Annotation::of(new Reflection_Class($class_name))->value
					);
				}
				if (!$same_table) {
					$this->updateTable($context_class_name, $mysqli);
				}
			}
		}
	}

	//--------------------------------------------------------------------------- updateContextTables
	/**
	 * @param $mysqli  Contextual_Mysqli
	 * @return boolean true if the query with an error can be retried after this error was dealt with
	 */
	private function updateContextTables(Contextual_Mysqli $mysqli) : bool
	{
		$context = end($mysqli->contexts);
		if (!is_array($context)) {
			$context = [$context];
		}
		foreach ($context as $context_class) {
			if (is_string($context_class) && $this->updateTable($context_class, $mysqli)) {
				$retry = true;
			}
			// TODO is_array($context_class) => updateImplicitTable (but how ? I only have two columns)
		}
		return isset($retry);
	}

	//--------------------------------------------------------------------------- updateImplicitTable
	/**
	 * @param $property           Reflection_Property a property with @link Map
	 * @param $exclude_class_name string no need to update this already updated class
	 * @param $mysqli             Contextual_Mysqli
	 */
	private function updateImplicitTable(
		Reflection_Property $property, string $exclude_class_name, Contextual_Mysqli $mysqli
	) : void
	{
		$link_table          = new Link_Table($property);
		$column_name         = $link_table->masterColumn();
		$foreign_column_name = $link_table->foreignColumn();
		$table_name          = $link_table->table();

		$class_name         = Builder::className($property->getFinalClassName());
		$foreign_class_name = Builder::className($property->getType()->getElementTypeAsString());

		// only the foreign class may be updated : the main has already been done by caller
		if ($foreign_class_name !== $exclude_class_name) {
			$this->updateTable($foreign_class_name, $mysqli, false);
		}

		$table = new Table($table_name);
		$table->addColumn(Column::buildLink($column_name));
		$table->addColumn(Column::buildLink($foreign_column_name));

		$ids_index = new Index();
		$ids_index->addKey($column_name);
		$ids_index->addKey($foreign_column_name);
		$ids_index->setType(Index::PRIMARY);
		$table->addIndex($ids_index);
		$table->addForeignKey(Foreign_Key::buildLink(
			$table_name, $column_name, $class_name
		));

		$second_index = new Index();
		$second_index->addKey($foreign_column_name);
		$table->addIndex($second_index);
		if (!$property->getType()->isAbstractClass()) {
			$table->addForeignKey(Foreign_Key::buildLink(
				$table_name, $foreign_column_name, $foreign_class_name
			));
		}

		// do not create empty implicit table if it does not already exist
		// will be automatically created on first needed use
		if ($this->create_empty_tables || $mysqli->exists($table_name)) {
			$mysqli->contexts[] = [$class_name, $foreign_class_name];
			$this->updateTableStructure($table, new Table_Builder_Class(), $mysqli);
			array_pop($mysqli->contexts);
		}
	}

	//-------------------------------------------------------------------------- updateImplicitTables
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class              Reflection_Class a class to scan properties for @link Map
	 * @param $exclude_class_name string no need to update this already updated class
	 * @param $mysqli             Contextual_Mysqli
	 */
	private function updateImplicitTables(
		Reflection_Class $class, string $exclude_class_name, Contextual_Mysqli $mysqli
	) : void
	{
		if (Class_\Link_Annotation::of($class)->value) {
			/** @noinspection PhpUnhandledExceptionInspection class is valid */
			$link_class = new Link_Class($class->name);
			$properties = $link_class->getLocalProperties();
		}
		else {
			$properties = $class->getProperties();
		}
		foreach (Replaces_Annotations::removeReplacedProperties($properties) as $property) {
			/** @var $property Reflection_Property */
			if (Link_Annotation::of($property)->isMap()) {
				$this->updateImplicitTable($property, $exclude_class_name, $mysqli);
			}
		}
	}

	//----------------------------------------------------------------------------------- updateTable
	/**
	 * Update table structure matching a data class
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @param $mysqli     Contextual_Mysqli|null If null, Dao::current()->getConnection() will be taken
	 * @param $implicit   boolean if true, update linked implicit tables (anti-recursion)
	 * @return boolean true if an update query has been generated and executed
	 */
	public function updateTable(
		string $class_name, Contextual_Mysqli $mysqli = null, bool $implicit = true
	) : bool
	{
		if (
			in_array($class_name, $this->exclude_classes, true)
			|| (new Type($class_name))->isAbstractClass()
		) {
			return false;
		}

		if ($this->verbose) {
			echo '<h5>' . ($this->simulation ? '[Simulate]' : '[Run]') . SP . $class_name . '</h5>';
		}

		if (!$mysqli) {
			$data_link = Dao::current();
			if ($data_link instanceof Link) {
				$mysqli = $data_link->getConnection();
			}
			else {
				trigger_error('Must call updateTable() with a valid $mysqli link', E_USER_ERROR);
			}
		}

		$result              = false;
		$table_builder_class = new Table_Builder_Class();
		$table_builder_class->exclude_class_names = $this->exclude_classes;
		$class_tables = $table_builder_class->build($class_name);
		foreach ($class_tables as $class_table) {
			$result = $this->updateTableStructure(
				$class_table, $table_builder_class, $mysqli, $class_name
			);
		}
		if ($implicit && $result) {
			/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
			$this->updateImplicitTables(new Reflection_Class($class_name), $class_name, $mysqli);
		}
		return $result;
	}

	//-------------------------------------------------------------------------- updateTableStructure
	/**
	 * @param $class_table         Table
	 * @param $table_builder_class Table_Builder_Class
	 * @param $mysqli              Contextual_Mysqli
	 * @param $class_name          string|null
	 * @return boolean
	 */
	private function updateTableStructure(
		Table $class_table, Table_Builder_Class $table_builder_class,
		Contextual_Mysqli $mysqli, string $class_name = null
	) : bool
	{
		$table_name  = $class_table->getName();
		$mysql_table = Table_Builder_Mysqli::build($mysqli, $table_name);
		// create table
		if (!$mysql_table) {
			$queries = (new Create_Table($class_table))->build();
			$mysqli->contexts[] = $class_name
				? array_merge($table_builder_class->dependencies_context, [$class_name])
				: $table_builder_class->dependencies_context;
			foreach ($queries as $query) {
				$this->updateContextAfterCreate($mysqli, $query, $class_name);
				$this->query($mysqli, $query);
			}
			array_pop($mysqli->contexts);
			$result = true;
		}

		// alter table
		else {
			$alter_primary_key  = true;
			$builder            = new Alter_Table($mysql_table);
			$mysql_columns      = $mysql_table->getColumns();
			$mysql_foreign_keys = $mysql_table->getForeignKeys();

			$result = $mysqli->query("SHOW CREATE TABLE `$table_name`");
			$row    = $result->fetch_row();
			$result->free();
			$create_table = end($row);
			if (!str_contains($create_table, 'DEFAULT CHARSET=' . Database::CHARACTER_SET)) {
				$builder->setCharacterSet(Database::CHARACTER_SET, Database::COLLATE);
			}
			else {
				$result = $mysqli->query("SHOW TABLE STATUS LIKE '$table_name'");
				$status = $result->fetch_assoc();
				$result->free();
				if ($status['Collation'] !== Database::COLLATE) {
					$builder->setCharacterSet(Database::CHARACTER_SET, Database::COLLATE);
				}
			}

			foreach ($class_table->getColumns() as $column) {
				$column_name = $column->getName();
				if (!isset($mysql_columns[$column_name])) {
					$builder->addColumn($column);
				}
				elseif (!$column->equiv($mysql_columns[$column_name])) {
					if ($this->notice) {
						$message = 'Maintainer alters column '
							. $mysql_table->getName() . '.' . $column->getName() . BRLF
							. PRE
							. print_r($mysql_columns[$column_name]->diffCombined($column), true)
							. _PRE;
						switch ($this->notice) {
							case self::VERBOSE:
								echo $message . BRLF;
								break;
							case self::WARNING:
								trigger_error($message);
						}
					}
					$builder->alterColumn($column_name, $column);
					if (isset($mysql_foreign_keys[$table_name . DOT . $column_name])) {
						$builder->alterForeignKey($mysql_foreign_keys[$table_name . DOT . $column_name]);
					}
					// if the column is already a primary key or if not wished : do not alter primary key
					if (
						($column->getName() === 'id')
						&& ($mysql_columns[$column_name]->isPrimaryKey() || !$column->isPrimaryKey())
					) {
						$alter_primary_key = false;
					}
				}
			}
			$class_foreign_keys = $class_table->getForeignKeys();
			foreach ($class_foreign_keys as $foreign_key_constraint => $foreign_key) {
				if (!isset($mysql_foreign_keys[$foreign_key_constraint])) {
					$builder->addForeignKey($foreign_key);
				}
				elseif (!$foreign_key->equiv($mysql_foreign_keys[$foreign_key_constraint])) {
					if ($this->notice) {
						$message = 'Maintainer alters foreign key constraint '
							. $mysql_table->getName() . '.' . $foreign_key_constraint . BRLF
							. PRE
							. print_r(
								$mysql_foreign_keys[$foreign_key_constraint]->diffCombined($foreign_key), true
							)
							. _PRE;
						switch ($this->notice) {
							case self::VERBOSE:
								echo $message . BRLF;
								break;
							case self::WARNING:
								trigger_error($message);
						}
					}
					$builder->alterForeignKey($foreign_key);
				}
			}
			foreach ($mysql_foreign_keys as $foreign_key_constraint => $foreign_key) {
				if (!isset($class_foreign_keys[$foreign_key_constraint])) {
					$builder->dropForeignKey($foreign_key);
				}
			}
			if ($builder->isReady()) {
				$mysqli->contexts[] = $class_name
					? array_merge($table_builder_class->dependencies_context, [$class_name])
					: $table_builder_class->dependencies_context;
				if ($builder->check($mysqli, $this->notice)) {
					foreach ($builder->build(true, $alter_primary_key) as $query) {
						$this->query($mysqli, $query);
					}
				}
				elseif ($this->notice) {
					$message = 'Ignored update of ' . ($class_name ?: 'implicit table') . SP . $table_name;
					switch ($this->notice) {
						case self::OUTPUT:
						case self::VERBOSE:
							echo '! ' . $message . BRLF;
							break;
						case self::WARNING:
							trigger_error($message, E_USER_WARNING);
					}
				}
				array_pop($mysqli->contexts);
				$result = true;
			}
			else {
				$result = false;
			}
		}
		if (
			$class_name
			&& method_exists($class_name, '__maintain')
			&& !(new Call_Stack)->containsMethod([$class_name, '__maintain'])
		) {
			call_user_func([$class_name, '__maintain']);
		}
		return $result;
	}

}
