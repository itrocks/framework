<?php
namespace SAF\Framework\Sql\Builder;

use SAF\Framework\Dao\Sql\Column;
use SAF\Framework\Dao\Sql\Table;

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
	 * @param $column Column
	 */
	public function alterColumn($old_column_name, Column $column)
	{
		$this->alter_columns[$old_column_name] = $column;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * Builds the SQL query to alter table
	 *
	 * @return string
	 */
	public function build()
	{
		$sqls = [];
		foreach ($this->add_columns as $add) {
			$sqls[] = 'ADD COLUMN ' . $add->toSql();
		}
		foreach ($this->alter_columns as $column_name => $alter) {
			$sqls[] = 'CHANGE COLUMN ' . BQ . $column_name . BQ . SP . $alter->toSql();
		}
		return 'ALTER TABLE ' . BQ . $this->table->getName() . BQ . LF . TAB
			. join(',' . LF . TAB, $sqls);
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
