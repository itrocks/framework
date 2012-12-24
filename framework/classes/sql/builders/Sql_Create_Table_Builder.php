<?php
namespace SAF\Framework;

class Sql_Create_Table_Builder
{

	//---------------------------------------------------------------------------------------- $table
	/**
	 * @var Dao_Table
	 */
	private $table;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(Dao_Table $table)
	{
		$this->table = $table;
	}

	//----------------------------------------------------------------------------------------- build
	public function build()
	{
		foreach ($this->table->getColumns() as $column) {
			$columns[] = $column->toSql();
		}
		$indexes = array();
		foreach ($this->table->getIndexes() as $index) {
			$indexes[] = $index->toSql();
		}
		return "CREATE TABLE `" . $this->table->getName() . "` ("
			. join(", ", $columns)
			. (isset($indexes) ? ", " : "") . join(", ", $indexes)
			. ")";
	}

}
