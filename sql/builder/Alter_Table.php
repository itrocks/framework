<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Dao\Sql\Column;
use ITRocks\Framework\Dao\Sql\Foreign_Key;
use ITRocks\Framework\Dao\Sql\Table;

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

	//-------------------------------------------------------------------------------- $alter_columns
	/**
	 * Columns to alter
	 *
	 * @var Column[] key is the old name of the column
	 */
	private $alter_columns = [];

	//--------------------------------------------------------------------------------- $foreign_keys
	/**
	 * Foreign keys linked to altered columns, that need to be dropped-re-created during alter
	 *
	 * @var Foreign_Key[]
	 */
	private $foreign_keys = [];

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

	//----------------------------------------------------------------------------------- alterColumn
	/**
	 * Adds an altered column
	 *
	 * @param $old_column_name string
	 * @param $column          Column
	 * @param $foreign_key     Foreign_Key
	 */
	public function alterColumn($old_column_name, Column $column, Foreign_Key $foreign_key = null)
	{
		$this->alter_columns[$old_column_name] = $column;
		if ($foreign_key) {
			$this->foreign_keys[$foreign_key->getFields()[0]] = $foreign_key;
		}
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
		foreach ($this->alter_columns as $column_name => $alter_column) {
			if ($alter) $alter .= ',' . LF;
			if (isset($this->foreign_keys[$column_name])) {
				$foreign_key     = $this->foreign_keys[$column_name];
				$reference_table = $foreign_key->getReferenceTable();
				if (strpos($lock_tables, BQ . $reference_table . BQ) === false) {
					$lock_tables .= ', ' . BQ . $reference_table . BQ . ' WRITE';
				}
				if ($constraints) $constraints .= ',' . LF;
				$constraints .= TAB . 'ADD' . SP . $foreign_key->toSql();
				$alter       .= TAB . $foreign_key->toDropSql() . ',' . LF;
			}
			$alter .= TAB;
			$alter .= ($column_name === $alter_column->getName())
				? 'MODIFY'
				: ('CHANGE COLUMN ' . BQ . $column_name . BQ);
			$alter .= SP . $alter_column->toSql($alter_primary_key);
		}
		$queries = [];
		if ($lock && strpos($lock_tables, ',')) {
			$queries['lock'] = 'LOCK TABLES ' . $lock_tables;
		}
		$queries['alter'] = 'ALTER TABLE' . SP . BQ . $table_name . BQ . LF . $alter;
		if ($constraints) {
			$queries['constraints'] = 'ALTER TABLE' . SP . BQ . $table_name . BQ . LF . $constraints;
		}
		if (isset($queries['lock'])) {
			$queries['unlock'] = 'UNLOCK TABLES';
		}
		return $queries;
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
		return $this->add_columns || $this->alter_columns;
	}

}
