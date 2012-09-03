<?php
namespace Framework;

class Mysql_Field implements Dao_Field
{

	private $Field;

	private $Type;

	private $Null;

	private $Key;

	private $Default;

	private $Extra;

	//--------------------------------------------------------------------------------------- getName
	public function getName()
	{
		return $this->Field;
	}

	//--------------------------------------------------------------------------------------- getType
	public function getType()
	{
		$type = lParse($this->Type, "(");
		switch ($type) {
			case "decimal": case "float": case "double":
				return "float";
			case "tinyint": case "smallint": case "mediumint": case "int": case "bigint":
				return "integer";
			case "char": case "varchar": case "tinytext": case "text": case "mediumtext": case "longtext":
			case "enum": case "set":
				return "string";
			case "date": case "datetime": case "timestamp": case "time": case "year":
				return "Date_Time";
			case "tinyblob": case "blob": case "mediumblob": case "longblob":
				return "string";
		}
	}

}
