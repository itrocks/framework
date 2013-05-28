<?php
namespace SAF\Framework;

/**
 * SQL create table queries builder
 */
class Sql_Create_Table_Builder
{

	//---------------------------------------------------------------------------------------- $table
	/**
	 * @var Dao_Table
	 */
	private $table;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $table Dao_Table
	 */
	public function __construct(Dao_Table $table)
	{
		$this->table = $table;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return string
	 */
	public function build()
	{
		$columns = array();
		foreach ($this->table->getColumns() as $column) {
			$columns[] = $column->toSql();
		}
		$indexes = array();
		foreach ($this->table->getIndexes() as $index) {
			$indexes[] = $index->toSql();
		}
		return "CREATE TABLE `" . $this->table->getName() . "` ("
			. join(", ", $columns)
			. ($indexes ? ", " : "") . join(", ", $indexes)
			. ") DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
	}

}
