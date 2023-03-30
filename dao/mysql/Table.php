<?php
namespace ITRocks\Framework\Dao\Mysql;

use AllowDynamicProperties;
use ITRocks\Framework\Dao\Sql;
use ITRocks\Framework\Reflection\Attribute\Property\Values;

/**
 * An object representation of a mysql table
 */
#[AllowDynamicProperties]
class Table implements Sql\Table
{

	//--------------------------------------------------------------------------------------- $Engine
	#[Values('ARCHIVE, BDB, CSV, FEDERATED, InnoDB, MyISAM, MEMORY, MERGE, NDBCluster')]
	private string $Engine;

	//----------------------------------------------------------------------------------------- $Name
	private string $Name;

	//-------------------------------------------------------------------------------------- $columns
	/** @var Column[] key is the column name */
	private array $columns;

	//--------------------------------------------------------------------------------- $foreign_keys
	/** @var Foreign_Key[] key is the column name */
	private array $foreign_keys = [];

	//-------------------------------------------------------------------------------------- $indexes
	/** @var Index[] key is the index name */
	private array $indexes = [];

	//----------------------------------------------------------------------------------- __construct
	public function __construct(string $name = null)
	{
		if (isset($name)) {
			$this->Name   = $name;
			$this->Engine = 'InnoDB';
		}
	}

	//------------------------------------------------------------------------------------- addColumn
	public function addColumn(Column $column) : void
	{
		$this->columns[$column->getName()] = $column;
	}

	//--------------------------------------------------------------------------------- addForeignKey
	public function addForeignKey(Foreign_Key $foreign_key) : void
	{
		$constraint = $foreign_key->getConstraint();
		if (isset($this->foreign_keys[$constraint])) {
			trigger_error("Foreign key $constraint overrides an existing one", E_USER_WARNING);
		}
		$this->foreign_keys[$constraint] = $foreign_key;
	}

	//-------------------------------------------------------------------------------------- addIndex
	public function addIndex(Index $index) : void
	{
		$this->indexes[$index->getName()] = $index;
	}

	//------------------------------------------------------------------------------------- getColumn
	public function getColumn(string $name) : Column
	{
		return $this->columns[$name];
	}

	//------------------------------------------------------------------------------------ getColumns
	/** @return Column[] */
	public function getColumns() : array
	{
		return $this->columns;
	}

	//-------------------------------------------------------------------------------- getForeignKeys
	/** @return Foreign_Key[] */
	public function getForeignKeys() : array
	{
		return $this->foreign_keys;
	}

	//------------------------------------------------------------------------------------ getIndexes
	/** @return Index[] */
	public function getIndexes() : array
	{
		return $this->indexes;
	}

	//--------------------------------------------------------------------------------------- getName
	public function getName() : string
	{
		return $this->Name;
	}

	//--------------------------------------------------------------------------------- getSqlPostfix
	public function getSqlPostfix() : string
	{
		return ' ENGINE=' . $this->Engine;
	}

	//------------------------------------------------------------------------------------- hasColumn
	public function hasColumn(string $name) : bool
	{
		return isset($this->columns[$name]);
	}

}
