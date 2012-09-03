<?php
namespace Framework;

interface Dao_Table
{

	//------------------------------------------------------------------------------------- getFields
	/**
	 * @param Mysql_Link $data_link
	 * @param string     $object_class
	 */
	public static function getFields($data_link, $object_class);

}
