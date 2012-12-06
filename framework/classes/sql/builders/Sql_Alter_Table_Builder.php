<?php
namespace SAF\Framework;

class Sql_Alter_Table_builder
{

	//---------------------------------------------------------------------------------- $add_columns
	/**
	 * Columns to add
	 *
	 * @var multitype::Dao_Column
	 */
	private $add_columns = array();

	//-------------------------------------------------------------------------------- $alter_columns
	/**
	 * Columns to alter
	 *
	 * @var multitype::Dao_Column key is the old name of the column
	 */
	private $alter_columns = array();

	//------------------------------------------------------------------------------------- addColumn
	/**
	 * Adds an added column
	 *
	 * @param Dao_Column $column
	 */
	public function addColumn(Dao_Column $column)
	{
		$this->add_columns[$column->getName()] = $column;
	}

	//------------------------------------------------------------------------------------- addColumn
	/**
	 * Adds an altered column
	 * 
	 * @param string $old_column_name
	 * @param Dao_Column $column
	 */
	public function alterColumn($old_column_name, Dao_Column $column)
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
		// TODO
	}

}
