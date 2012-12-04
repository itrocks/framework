<?php
namespace SAF\Framework;

class Mysql_Table
{

	//-------------------------------------------------------------------------------------- $columns
	/**
	 * @var multitype:Mysql_Column key is the column name
	 */
	public $columns;

	//-------------------------------------------------------------------------------------- $indexes
	/**
	 * @var multitype:Mysql_Index key is the index name
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
	public function __construct($name = null)
	{
		if (isset($name)) {
			$this->Name = $name;
			$this->Engine = "InnoDB";
		}
	}

	//--------------------------------------------------------------------------------------- getName
	public function getName()
	{
		return $this->Name;
	}

}
