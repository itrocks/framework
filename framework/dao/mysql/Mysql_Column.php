<?php
namespace SAF\Framework;

/**
 * Mysql column
 */
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
	 *
	 * Value can be "tinyint(l)", "smallint(l)", "mediumint(l)", "int(l)", "integer(l)", "bigint(l)",
	 * "float(p)", "double", "real", "decimal(l,d)", "numeric(l,d)", "bit(l)",
	 * "date", "time", "datetime", "timestamp", "year",
	 * "char", "binary(l)", "varchar(l)", "varbinary(l)",
	 * "tinyblob", "tinytext", "blob", "text", "mediumblob", "mediumtext", "longblob", "longtext",
	 * "enum('v',...)", "set('v',...)"
	 * where "p" = "precision", "l" = "length", "d" = "decimals", "v" = "value"
	 * numeric types can be followed with " unsigned"
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

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name string
	 * @param $type string
	 */
	public function __construct($name = null, $type = null)
	{
		if (isset($name)) $this->Field = $name;
		if (isset($type)) $this->Type  = $type;
		$this->cleanupDefault();
	}

	//------------------------------------------------------------------------------------- canBeNull
	/**
	 * @return bool
	 */
	public function canBeNull()
	{
		return $this->Null === "YES";
	}

	//-------------------------------------------------------------------------------- cleanupDefault
	/**
	 * Gives the default value the correct type
	 *
	 * @example replace "0" by 0 for a numeric value (ie mysqli->fetch_object gets Default as string)
	 * @return Mysql_Column
	 */
	private function cleanupDefault()
	{
		if (isset($this->Default)) {
			if ($this->getType()->isNumeric()) {
				$this->Default += 0;
			}
		}
		return $this;
	}

	//----------------------------------------------------------------------------------------- equiv
	/**
	 * Returns true if the column is an equivalent of the other column
	 *
	 * @param $column Mysql_Column
	 * @return bool
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
	/**
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		return $this->Default;
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->Field;
	}

	//--------------------------------------------------------------------------------- getSqlPostfix
	/**
	 * @return string
	 */
	public function getSqlPostfix()
	{
		return $this->Extra ? " " . $this->Extra : "";
	}

	//------------------------------------------------------------------------------------ getSqlType
	/**
	 * @return string
	 */
	public function getSqlType()
	{
		return $this->Type;
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * @return Type
	 */
	public function getType()
	{
		$i = strpos($this->Type, "(");
		$type = ($i === false) ? $this->Type : substr($this->Type, 0, $i);
		switch ($type) {
			case "decimal": case "float": case "double":
				return new Type("float");
			case "tinyint": case "smallint": case "mediumint": case "int": case "bigint":
				return new Type("integer");
			case "enum": case "set":
				return (new Type("string"))->multiple();
			case "date": case "datetime": case "timestamp": case "time": case "year":
				return new Type('SAF\Framework\Date_Time');
			default:
				return new Type("string");
		}
	}

	//---------------------------------------------------------------------------------------- hasKey
	/**
	 * @return bool
	 */
	public function hasKey()
	{
		return !empty($this->Key);
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * @return string
	 */
	public function toSql()
	{
		$column_name = $this->getName();
		$type = $this->getSqlType();
		$postfix = $this->getSqlPostfix();
		$sql = "`" . $column_name . "` " . $type;
		if (!$this->canBeNull()) {
			$sql .= " NOT NULL";
		}
		if ($postfix != " auto_increment") {
			$sql .= " DEFAULT " . Sql_Value::escape($this->getDefaultValue());
		}
		$sql .= $postfix;
		if ($postfix === " auto_increment") {
			$sql .= " PRIMARY KEY";
		}
		return $sql;
	}

}
