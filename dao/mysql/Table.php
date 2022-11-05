<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Dao\Sql;

/**
 * An object representation of a mysql table
 */
class Table implements Sql\Table
{

	//--------------------------------------------------------------------------------------- $Engine
	/**
	 * @values 'ARCHIVE', 'BDB', 'CSV', 'FEDERATED', 'InnoDB', 'MyISAM', 'MEMORY', 'MERGE', 'NDBCluster'
	 * @var string
	 */
	private string $Engine;

	//----------------------------------------------------------------------------------------- $Name
	/**
	 * @var string
	 */
	private string $Name;

	//-------------------------------------------------------------------------------------- $columns
	/**
	 * @var Column[] key is the column name
	 */
	private array $columns;

	//--------------------------------------------------------------------------------- $foreign_keys
	/**
	 * @var Foreign_Key[] key is the column name
	 */
	private array $foreign_keys = [];

	//-------------------------------------------------------------------------------------- $indexes
	/**
	 * @var Index[] key is the index name
	 */
	private array $indexes = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name string|null
	 */
	public function __construct(string $name = null)
	{
		if (isset($name)) {
			$this->Name   = $name;
			$this->Engine = 'InnoDB';
		}
	}

	//------------------------------------------------------------------------------------- addColumn
	/**
	 * @param $column Column
	 */
	public function addColumn(Column $column) : void
	{
		$this->columns[$column->getName()] = $column;
	}

	//--------------------------------------------------------------------------------- addForeignKey
	/**
	 * @param $foreign_key Foreign_Key
	 */
	public function addForeignKey(Foreign_Key $foreign_key) : void
	{
		$constraint = $foreign_key->getConstraint();
		if (isset($this->foreign_keys[$constraint])) {
			trigger_error("Foreign key $constraint overrides an existing one", E_USER_WARNING);
		}
		$this->foreign_keys[$constraint] = $foreign_key;
	}

	//-------------------------------------------------------------------------------------- addIndex
	/**
	 * @param $index Index
	 */
	public function addIndex(Index $index) : void
	{
		$this->indexes[$index->getName()] = $index;
	}

	//------------------------------------------------------------------------------------- getColumn
	/**
	 * @param $name string
	 * @return Column
	 */
	public function getColumn(string $name) : Column
	{
		return $this->columns[$name];
	}

	//------------------------------------------------------------------------------------ getColumns
	/**
	 * @return Column[]
	 */
	public function getColumns() : array
	{
		return $this->columns;
	}

	//-------------------------------------------------------------------------------- getForeignKeys
	/**
	 * @return Foreign_Key[]
	 */
	public function getForeignKeys() : array
	{
		return $this->foreign_keys;
	}

	//------------------------------------------------------------------------------------ getIndexes
	/**
	 * @return Index[]
	 */
	public function getIndexes() : array
	{
		return $this->indexes;
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName() : string
	{
		return $this->Name;
	}

	//--------------------------------------------------------------------------------- getSqlPostfix
	/**
	 * @return string
	 */
	public function getSqlPostfix() : string
	{
		return ' ENGINE=' . $this->Engine;
	}

	//------------------------------------------------------------------------------------- hasColumn
	/**
	 * @param $name string
	 * @return boolean
	 */
	public function hasColumn(string $name) : bool
	{
		return isset($this->columns[$name]);
	}

}
