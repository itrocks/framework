<?php
namespace SAF\Framework;

/**
 * An object representation of a mysql table
 */
class Mysql_Table implements Dao_Table
{

	//-------------------------------------------------------------------------------------- $columns
	/**
	 * @var Mysql_Column[] key is the column name
	 */
	public $columns;

	//-------------------------------------------------------------------------------------- $indexes
	/**
	 * @var Mysql_Index[] key is the index name
	 */
	public $indexes;

	//--------------------------------------------------------------------------------------- $Engine
	/**
	 * @var string
	 * @values "ARCHIVE", "BDB", "CSV", "FEDERATED", "InnoDB", "MyISAM", "MEMORY", "MERGE", "NDBCluster"
	 */
	private $Engine;

	//----------------------------------------------------------------------------------------- $Name
	/**
	 * @var string
	 */
	private $Name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name string
	 */
	public function __construct($name = null)
	{
		if (isset($name)) {
			$this->Name = $name;
			$this->Engine = "InnoDB";
		}
	}

	//------------------------------------------------------------------------------------- addColumn
	/**
	 * @param $column Mysql_Column
	 */
	public function addColumn(Mysql_Column $column)
	{
		$this->columns[] = $column;
	}

	//------------------------------------------------------------------------------------ getColumns
	/**
	 * @return Mysql_Column[]
	 */
	public function getColumns()
	{
		return $this->columns;
	}

	//------------------------------------------------------------------------------------ getIndexes
	/**
	 * @return Mysql_Index[]
	 */
	public function getIndexes()
	{
		return is_array($this->indexes) ? $this->indexes : array();
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->Name;
	}

	//--------------------------------------------------------------------------------- getSqlPostfix
	/**
	 * @return string
	 */
	public function getSqlPostfix()
	{
		return " ENGINE=" . $this->Engine;
	}

}
