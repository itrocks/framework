<?php
namespace SAF\Framework;

class Mysql_Index implements Dao_Index
{

	//------------------------------------------------------------------------------------- $Key_name
	/**
	 * @var string
	 */
	private $Key_name;

	//----------------------------------------------------------------------------------------- $keys
	/**
	 * @var multitype:Mysql_Key
	 */
	public $keys;

	//--------------------------------------------------------------------------------------- getKeys
	public function getKeys()
	{
		return $this->keys;
	}

	//--------------------------------------------------------------------------------------- getName
	public function getName()
	{
		return $this->Key_name;
	}

}
