<?php
namespace SAF\Framework;

class Sql_Create_Table_Builder
{

	//---------------------------------------------------------------------------------------- $table
	/**
	 * @var Table
	 */
	private $table;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(Table $table)
	{
		$this->table = $table;
	}

	//----------------------------------------------------------------------------------------- build
	public function build()
	{
		$table_name = $this->table->getName();
		$sql = "CREATE TABLE `$table_name` (";
		$first = true;
		foreach ($this->table->getColumns() as $column) {
			if ($first) {
				$first = false;
			}
			else {
				$sql .= ", ";
			}
			$column_name = $column->getName();
			$type = $column->getType();
			$sql .= "`$column_name` $type";
			if (!$column->canBeNull()) {
				$sql .= " NOT NULL";
			}
			$sql .= " DEFAULT " . Sql_Value::escape($column->getDefault());
		}
		$sql .= ")";
		return $sql;
	}

}
