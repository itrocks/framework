<?php
namespace SAF\Framework\Sql\Builder;

use SAF\Framework\Dao\Sql\Table;

/**
 * SQL create table queries builder
 */
class Create_Table
{

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

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return string
	 */
	public function build()
	{
		$columns = [];
		foreach ($this->table->getColumns() as $column) {
			$columns[] = $column->toSql();
		}
		$indexes = [];
		foreach ($this->table->getIndexes() as $index) {
			$indexes[] = $index->toSql();
		}
		$foreign_keys = [];
		foreach ($this->table->getForeignKeys() as $foreign_key) {
			$foreign_keys[] = $foreign_key->toSql();
		}
		return 'CREATE TABLE IF NOT EXISTS ' . BQ . $this->table->getName() . BQ . ' ('
			. join(', ', $columns)
			. ($indexes ? ', ' : '') . join(', ', $indexes)
			. ($foreign_keys ? ', ' : '') . join(', ', $foreign_keys)
			. ') DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
	}

}
