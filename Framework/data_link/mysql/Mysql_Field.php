<?php

class Mysql_Field implements DAO_Field
{

	private $Name;

	private $Type;

	private $Null;

	private $Key;

	private $Default;

	private $Extra;

	//--------------------------------------------------------------------------------------- getName
	public function getName()
	{
		return $this->Name;
	}

	//--------------------------------------------------------------------------------------- getType
	public function getType()
	{
		$type = lParse($this->Type, "(");
		switch ($type) {
			case "decimal": case "float": case "double":
				return Data_Type::FLOAT;
			case "tinyint": case "smallint": case "mediumint": case "int": case "bigint":
				return Data_Type::INT;
			case "char": case "varchar": case "tinytext": case "text": case "mediumtext": case "longtext":
			case "enum": case "set":
				return Data_Type::TEXT;
			case "date": case "datetime": case "timestamp": case "time": case "year":
				return Data_Type::TIME;
			case "tinyblob": case "blob": case "mediumblob": case "longblob":
				return Data_Type::RAW;
		}
	}

}
