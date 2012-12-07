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
	public function __construct(Dao_Table $table)
	{
		$this->table = $table;
	}

	//----------------------------------------------------------------------------------------- build
	public function build()
	{
		$table_name = $this->table->getName();
		$sql = "CREATE TABLE `$table_name` (";
		$first = true;
		$final = "";
		foreach ($this->table->getColumns() as $column) {
			if ($first) {
				$first = false;
			}
			else {
				$sql .= ", ";
			}
			$column_name = $column->getName();
			$type = $column->getSqlType();
			$postfix = $column->getSqlPostfix();
			$sql .= "`" . $column_name. "` " . $type;
			if (!$column->canBeNull()) {
				$sql .= " NOT NULL";
			}
			if ($postfix != " auto_increment") {
				$sql .= " DEFAULT " . Sql_Value::escape($column->getDefaultValue());
			}
			$sql .= $postfix;
			if ($postfix == " auto_increment") {
				$final = ", PRIMARY KEY (`" . $column_name . "`)";
			}
		}
		$sql .= $final . ")";
		return $sql;
	}

}
