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
	private $columns;

	//--------------------------------------------------------------------------------- $foreign_keys
	/**
	 * @var Mysql_Foreign_Key[] key is the column name
	 */
	private $foreign_keys;

	//-------------------------------------------------------------------------------------- $indexes
	/**
	 * @var Mysql_Index[] key is the index name
	 */
	private $indexes;

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
		$this->columns[$column->getName()] = $column;
	}

	//--------------------------------------------------------------------------------- addForeignKey
	/**
	 * @param $foreign_key Mysql_Foreign_Key
	 */
	public function addForeignKey(Mysql_Foreign_Key $foreign_key)
	{
		$this->foreign_keys[$foreign_key->getConstraint()] = $foreign_key;
	}

	//-------------------------------------------------------------------------------------- addIndex
	/**
	 * @param $index Mysql_Index
	 */
	public function addIndex(Mysql_Index $index)
	{
		$this->indexes[$index->getName()] = $index;
	}

	//------------------------------------------------------------------------------------ getColumns
	/**
	 * @return Mysql_Column[]
	 */
	public function getColumns()
	{
		return $this->columns;
	}

	//-------------------------------------------------------------------------------- getForeignKeys
	/**
	 * @return Mysql_Foreign_Key[]
	 */
	public function getForeignKeys()
	{
		return is_array($this->foreign_keys) ? $this->foreign_keys : array();
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
