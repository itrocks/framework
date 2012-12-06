<?php
namespace SAF\Framework;

class Mysql_Column implements Dao_Column
{

	//---------------------------------------------------------------------------------------- $Field
	/**
	 * Mysql column name
	 *
	 * @var string
	 */
	private $Field;

	//----------------------------------------------------------------------------------------- $Type
	/**
	 * Mysql data type
	 * Value can be "TINYINT", "SMALLINT", "MEDIUMINT", "INT", "INTEGER", "BIGINT",
	 * "FLOAT(p)", "DOUBLE", "REAL", "DECIMAL(l,d)", "NUMERIC(l,d)", "BIT(l)",
	 * "DATE", "TIME", "DATETIME", "TIMESTAMP", "YEAR",
	 * "CHAR", "BINARY(l)", "VARCHAR(l)", "VARBINARY(l)",
	 * "TINYBLOB", "TINYTEXT", "BLOB", "TEXT", "MEDIUMBLOB", "MEDIUMTEXT", "LONGBLOB", "LONGTEXT",
	 * "ENUM('v',...)", "SET('v',...)"
	 * Where "p" = "precision", "l" = "length", "d" = "decimals", "v" = "value".
	 *
	 * @var string
	 */
	private $Type;

	//----------------------------------------------------------------------------------------- $Null
	/**
	 * Can the data be null ?
	 *
	 * @var string
	 * @values YES, NO
	 */
	private $Null;

	//------------------------------------------------------------------------------------------ $Key
	/**
	 * Is the data part of an index key ?
	 *
	 * @var string
	 * @values PRI, MUL, UNI,
	 */
	private $Key;

	//-------------------------------------------------------------------------------------- $Default
	/**
	 * Default value for the column
	 * May be empty, null, or a value of the same type as the column.
	 *
	 * @var mixed
	 */
	private $Default;

	//---------------------------------------------------------------------------------------- $Extra
	/**
	 * Extra options to the column
	 * A list of options, the most common is "auto_increment" for primary auto-increment indexes.
	 *
	 * @var string
	 * @values auto_increment
	 */
	private $Extra;

	//------------------------------------------------------------------------------------- canBeNull
	public function canBeNull()
	{
		return $this->Null == "Yes";
	}

	//----------------------------------------------------------------------------------------- equiv
	/**
	 * Returns true if the column is an equivalent of the other column
	 *
	 * @param Mysql_Column $column
	 */
	public function equiv($column)
	{
		return ($this->Field === $column->Field)
			&& ($this->Type    === $column->Type)
			&& ($this->Null    === $column->Null)
			&& ($this->Default === $column->Default)
			&& ($this->Extra   === $column->Extra);
	}

	//------------------------------------------------------------------------------- getDefaultValue
	public function getDefaultValue()
	{
		return $this->Default;
	}

	//--------------------------------------------------------------------------------------- getName
	public function getName()
	{
		return $this->Field;
	}

	//------------------------------------------------------------------------------------ getSqlType
	public function getSqlType()
	{
		return $this->Type;
	}

	//--------------------------------------------------------------------------------------- getType
	public function getType()
	{
		$i = strpos($this->Type, "(");
		$type = ($i === false) ? $this->Type : substr($this->Type, 0, $i);
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
