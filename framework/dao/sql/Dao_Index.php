<?php
namespace SAF\Framework;

interface Dao_Index
{

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName();

	//--------------------------------------------------------------------------------------- getKeys
	/**
	 * @return multitype:Dao_Key
	 */
	public function getKeys();

}
