<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Dao\Sql\Column;
use ITRocks\Framework\Dao\Sql\Foreign_Key;
use ITRocks\Framework\Dao\Sql\Table;
use mysqli;

/**
 * SQL alter table queries builder
 */
class Alter_Table
{

	//---------------------------------------------------------------------------------- $add_columns
	/**
	 * Columns to add
	 *
	 * @var Column[]
	 */
	private $add_columns = [];

	//----------------------------------------------------------------------------- $add_foreign_keys
	/**
	 * Foreign keys linked to altered columns, that need to be dropped-re-created during alter
	 *
	 * @var Foreign_Key[]
	 */
	private $add_foreign_keys = [];

	//-------------------------------------------------------------------------------- $alter_columns
	/**
	 * Columns to alter
	 *
	 * @var Column[] key is the old name of the column
	 */
	private $alter_columns = [];

	//---------------------------------------------------------------------------- $drop_foreign_keys
	/**
	 * Foreign keys to be removed
	 *
	 * A foreign key may be dropped and added the same time : drop will always be done before add
	 *
	 * @var Foreign_Key[]
	 */
	private $drop_foreign_keys = [];

	//---------------------------------------------------------------------------- $set_character_set
	/**
	 * Alter table character set (ie UTF8)
	 *
	 * @example DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
	 * @var string
	 */
	private $set_character_set = null;

	//---------------------------------------------------------------------------------------- $table
	/**
	 * @var Table
	 */
	private $table;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $table Table
	 */
	public function __construct(Table $table)
	{
		$this->table = $table;
	}

	//------------------------------------------------------------------------------------- addColumn
	/**
	 * Adds an added column
	 *
	 * @param $column Column
	 */
	public function addColumn(Column $column)
	{
		$this->add_columns[$column->getName()] = $column;
	}

	//--------------------------------------------------------------------------------- addForeignKey
	/**
	 * Adds a new foreign key
	 *
	 * @param $foreign_key Foreign_Key
	 */
	public function addForeignKey(Foreign_Key $foreign_key)
	{
		$this->add_foreign_keys[$foreign_key->getFields()[0]] = $foreign_key;
	}

	//----------------------------------------------------------------------------------- alterColumn
	/**
	 * Adds an altered column
	 *
	 * @param $old_column_name string
	 * @param $column          Column
	 */
	public function alterColumn($old_column_name, Column $column)
	{
		$this->alter_columns[$old_column_name] = $column;
	}

	//------------------------------------------------------------------------------- alterForeignKey
	/**
	 * @param $foreign_key Foreign_Key
	 */
	public function alterForeignKey(Foreign_Key $foreign_key)
	{
		$this->dropForeignKey($foreign_key);
		$this->addForeignKey($foreign_key);
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * Builds the SQL queries to alter table
	 * Includes
	 *
	 * @param $lock              boolean if true, will lock tables before drop-create constraints
	 * @param $alter_primary_key boolean if false, will not send the PRIMARY KEY field modifier
	 * @return string[]
	 */
	public function build($lock = false, $alter_primary_key = true)
	{
		$table_name = $this->table->getName();
		$alter       = '';
		$constraints = '';
		$lock_tables = BQ . $table_name . BQ . ' WRITE';
		foreach ($this->add_columns as $add) {
			if ($alter) $alter .= ',' . LF;
			$alter .= TAB . 'ADD COLUMN ' . $add->toSql();
		}
		foreach ($this->drop_foreign_keys as $foreign_key) {
			$this->sqlAddLockTable($lock_tables, $foreign_key->getReferenceTable());
			if ($alter) $alter .= ',' . LF;
			$alter .= TAB . $foreign_key->toDropSql();
		}
		foreach ($this->alter_columns as $column_name => $alter_column) {
			if ($alter) $alter .= ',' . LF;
			$alter .= TAB;
			$alter .= ($column_name === $alter_column->getName())
				? 'MODIFY'
				: ('CHANGE COLUMN ' . BQ . $column_name . BQ);
			$alter .= SP . $alter_column->toSql($alter_primary_key);
		}
		foreach ($this->add_foreign_keys as $foreign_key) {
			$this->sqlAddLockTable($lock_tables, $foreign_key->getReferenceTable());
			if ($constraints) $constraints .= ',' . LF;
			$constraints .= TAB . 'ADD' . SP . $foreign_key->toSql();
		}
		if ($this->set_character_set) {
			if ($alter) $alter .= ',' . LF;
			$alter .= TAB . $this->set_character_set;
		}
		$queries = [];
		if ($lock && str_contains($lock_tables, ',')) {
			$queries['lock'] = 'LOCK TABLES ' . $lock_tables;
		}
		if ($alter) {
			$queries['alter'] = 'ALTER TABLE' . SP . BQ . $table_name . BQ . LF . $alter;
		}
		if ($constraints) {
			$queries['link'] = 'ALTER TABLE' . SP . BQ . $table_name . BQ . LF . $constraints;
		}
		if (isset($queries['lock'])) {
			$queries['unlock'] = 'UNLOCK TABLES';
		}
		return $queries;
	}

	//----------------------------------------------------------------------------------------- check
	/**
	 * Check if the changes will crash or lose data
	 *
	 * @param $mysqli mysqli
	 * @param $notice string @values Maintainer::const local
	 * @return boolean true if table structure modification will not crash or break stored data
	 */
	public function check(mysqli $mysqli, $notice)
	{
		$result = true;
		// all checks must be done, so do not use || or && operations
		if (!$this->checkForeignKeys($mysqli, $notice)) {
			$result = false;
		}
		if (!$this->checkTypes($mysqli, $notice)) {
			$result = false;
		}
		if (!$this->checkValues($mysqli, $notice)) {
			$result = false;
		}
		return $result;
	}

	//------------------------------------------------------------------------------ checkForeignKeys
	/**
	 * Check if adding foreign keys will crash because of orphan records
	 *
	 * @param $mysqli mysqli
	 * @param $notice string @values Maintainer::const local
	 * @return boolean true if adding foreign keys will not result in SQL errors because of orphans
	 */
	protected function checkForeignKeys(mysqli $mysqli, $notice)
	{
		$orphans_count = 0;
		$table_name    = $this->table->getName();

		foreach ($this->add_foreign_keys as $foreign_key) {
			$do_not_count  = true;
			$foreign_table = $foreign_key->getReferenceTable();
			$foreign_field = $foreign_key->getReferenceFields()[0];
			$source_field  = $foreign_key->getFields()[0];

			$result = $mysqli->query("SHOW CREATE TABLE `$foreign_table`");
			$row    = $result->fetch_row();
			$create = end($row);
			$result->free();
			if (!str_contains($create, ') ENGINE=InnoDB')) {
				$error_message = "Could not add foreign key from $table_name"
					. " to non-InnoDB $foreign_table table";
				switch ($notice) {
					case 'verbose': echo '! ' . $error_message . BRLF; break;
					case 'warning': trigger_error($error_message, E_USER_NOTICE);
				}
				$orphans_count ++;
				$do_not_count = true;
			}

			$check_fields_queries = [
				"SHOW FIELDS FROM `$table_name` LIKE '$source_field'",
				"SHOW FIELDS FROM `$foreign_table` LIKE '$foreign_field'"
			];
			$fields_count = 0;
			foreach ($check_fields_queries as $check_fields_query) {
				$result = $mysqli->query($check_fields_query);
				if ($result->fetch_row()) {
					$fields_count ++;
				}
				$result->free();
			}

			if (($fields_count === 2) && !$do_not_count) {

				$check_query = "
					SELECT COUNT(*)
					FROM `$table_name`
					WHERE ($source_field IS NOT NULL)
					AND ($source_field NOT IN (SELECT $foreign_field FROM `$foreign_table`))
				";
				$result = $mysqli->query($check_query);
				$row    = $result->fetch_row();
				$count  = reset($row);
				$result->free();

				if ($count) {
					$detail_query = "
						SELECT $source_field, COUNT(*)
						FROM `$table_name`
						WHERE ($source_field IS NOT NULL)
						AND ($source_field NOT IN (SELECT $foreign_field FROM `$foreign_table`))
						GROUP BY $source_field
						WITH ROLLUP
					";
					$error_message = "Could not add foreign key from $table_name.$source_field :"
						. " $count orphans $foreign_table.$foreign_field called from $table_name.$source_field"
						. " [$detail_query]";
					switch ($notice) {
						case 'output': case 'verbose': echo '! ' . $error_message . BRLF; break;
						case 'warning': trigger_error($error_message, E_USER_WARNING);
					}
					$orphans_count += $count;
				}
			}
		}

		$result = $mysqli->query("SHOW CREATE TABLE `$table_name`");
		$row    = $result->fetch_assoc();
		$create = $row['Create Table'] ?? '';
		$result->free();
		if ($create && !str_contains($create, ') ENGINE=InnoDB')) {
			$error_message = "Could not add foreign key from $table_name non-InnoDB table";
			switch ($notice) {
				case 'output':  echo '! ' . $error_message . BRLF; break;
				case 'warning': trigger_error($error_message);
			}
			$orphans_count ++;
		}

		return ($orphans_count === 0);
	}

	//------------------------------------------------------------------------------------ checkTypes
	/**
	 * Check if reducing size of fields will not break data
	 *
	 * @param $mysqli mysqli
	 * @param $notice string @values Maintainer::const local
	 * @return boolean true if data will not be destroyed by the types modifications
	 */
	protected function checkTypes(
		/** @noinspection PhpUnusedParameterInspection */ mysqli $mysqli, $notice
	) {
		// TODO search data that will be broken by the reduction
		/*
		if ($this->alter_columns) {
			$old_table = Table_Builder_Mysqli::build($mysqli, $this->table->getName());
			foreach ($this->alter_columns as $column) {
				if ($old_table->getColumn($column->getName())->reduces($column)) {
				}
			}
		}
		*/
		return true;
	}

	//----------------------------------------------------------------------------------- checkValues
	/**
	 * Check if changing values for ENUM and SET will not break data
	 *
	 * @param $mysqli mysqli
	 * @param $notice string @values Maintainer::const local
	 * @return boolean true if data will not be destroyed by the values modifications
	 */
	protected function checkValues(
		/** @noinspection PhpUnusedParameterInspection */ mysqli $mysqli, $notice
	) {
		// TODO compare old and new column values : if some are removed, check if they were used
		return true;
	}

	//-------------------------------------------------------------------------------- dropForeignKey
	/**
	 * Drops a foreign key
	 *
	 * @param $foreign_key Foreign_Key
	 */
	public function dropForeignKey(Foreign_Key $foreign_key)
	{
		$this->drop_foreign_keys[$foreign_key->getFields()[0]] = $foreign_key;
	}

	//--------------------------------------------------------------------------------------- isReady
	/**
	 * Returns true if the builder is ready to build
	 *
	 * The builder is ready if there is something to do
	 *
	 * @return boolean
	 */
	public function isReady()
	{
		return $this->add_columns
			|| $this->add_foreign_keys
			|| $this->alter_columns
			|| $this->drop_foreign_keys
			|| $this->set_character_set;
	}

	//------------------------------------------------------------------------------- setCharacterSet
	/**
	 * @param $character_set string
	 * @param $collate       string
	 */
	public function setCharacterSet($character_set, $collate)
	{
		$this->set_character_set = "DEFAULT CHARSET=$character_set COLLATE=$collate";
	}

	//------------------------------------------------------------------------------- sqlAddLockTable
	/**
	 * @param $lock_tables string a list of table names, back-quoted and separated by ', '
	 * @param $table       string a table name
	 */
	protected function sqlAddLockTable(&$lock_tables, $table)
	{
		if (!str_contains($lock_tables, BQ . $table . BQ)) {
			$lock_tables .= ', ' . BQ . $table . BQ . ' WRITE';
		}
	}

}
