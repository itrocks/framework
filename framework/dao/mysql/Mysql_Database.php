<?php
namespace SAF\Framework;

/**
 * Mysql database
 */
class Mysql_Database implements Dao_Database
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
