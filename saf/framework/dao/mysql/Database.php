<?php
namespace SAF\Framework\Dao\Mysql;

use SAF\Framework\Dao\Sql;

/**
 * Mysql database
 */
class Database implements Sql\Database
{

	//------------------------------------------------------------------------------------- $Database
	/**
	 * @var string
	 */
	private $Database;

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->Database;
	}

}
