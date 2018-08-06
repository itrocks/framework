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
			$alter .= $foreign_key->toDropSql();
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
		$queries = [];
		if ($lock && strpos($lock_tables, ',')) {
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
			|| $this->drop_foreign_keys;
	}

	//------------------------------------------------------------------------------- sqlAddLockTable
	/**
	 * @param $lock_tables string a list of table names, back-quoted and separated by ', '
	 * @param $table       string a table name
	 */
	protected function sqlAddLockTable(&$lock_tables, $table)
	{
		if (strpos($lock_tables, BQ . $table . BQ) === false) {
			$lock_tables .= ', ' . BQ . $table . BQ . ' WRITE';
		}
	}

}
