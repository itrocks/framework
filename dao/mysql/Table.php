<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Dao\Sql;

/**
 * An object representation of a mysql table
 */
class Table implements Sql\Table
{

	//-------------------------------------------------------------------------------------- $columns
	/**
	 * @var Column[] key is the column name
	 */
	private $columns;

	//--------------------------------------------------------------------------------- $foreign_keys
	/**
	 * @var Foreign_Key[] key is the column name
	 */
	private $foreign_keys;

	//-------------------------------------------------------------------------------------- $indexes
	/**
	 * @var Index[] key is the index name
	 */
	private $indexes;

	//--------------------------------------------------------------------------------------- $Engine
	/**
	 * @values 'ARCHIVE', 'BDB', 'CSV', 'FEDERATED', 'InnoDB', 'MyISAM', 'MEMORY', 'MERGE', 'NDBCluster'
	 * @var string
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
			$this->Engine = 'InnoDB';
		}
	}

	//------------------------------------------------------------------------------------- addColumn
	/**
	 * @param $column Column
	 */
	public function addColumn(Column $column)
	{
		$this->columns[$column->getName()] = $column;
	}

	//--------------------------------------------------------------------------------- addForeignKey
	/**
	 * @param $foreign_key Foreign_Key
	 */
	public function addForeignKey(Foreign_Key $foreign_key)
	{
		$this->foreign_keys[$foreign_key->getConstraint()] = $foreign_key;
	}

	//-------------------------------------------------------------------------------------- addIndex
	/**
	 * @param $index Index
	 */
	public function addIndex(Index $index)
	{
		$this->indexes[$index->getName()] = $index;
	}

	//------------------------------------------------------------------------------------- getColumn
	/**
	 * @param $name string
	 * @return Column
	 */
	public function getColumn($name)
	{
		return $this->columns[$name];
	}

	//------------------------------------------------------------------------------------ getColumns
	/**
	 * @return Column[]
	 */
	public function getColumns()
	{
		return $this->columns;
	}

	//-------------------------------------------------------------------------------- getForeignKeys
	/**
	 * @return Foreign_Key[]
	 */
	public function getForeignKeys()
	{
		return is_array($this->foreign_keys) ? $this->foreign_keys : [];
	}

	//------------------------------------------------------------------------------------ getIndexes
	/**
	 * @return Index[]
	 */
	public function getIndexes()
	{
		return is_array($this->indexes) ? $this->indexes : [];
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
		return ' ENGINE=' . $this->Engine;
	}

	//------------------------------------------------------------------------------------- hasColumn
	/**
	 * @param $name string
	 * @return boolean
	 */
	public function hasColumn($name)
	{
		return isset($this->columns[$name]);
	}

}
