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
	 *
	 * Value can be "tinyint(l)", "smallint(l)", "mediumint(l)", "int(l)", "integer(l)", "bigint(l)",
	 * "float(p)", "double", "real", "decimal(l,d)", "numeric(l,d)", "bit(l)",
	 * "date", "time", "datetime", "timestamp", "year",
	 * "char", "binary(l)", "varchar(l)", "varbinary(l)",
	 * "tinyblob", "tinytext", "blob", "text", "mediumblob", "mediumtext", "longblob", "longtxt",
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
	 * @values PRI, MUL, UNI
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
	public function __construct()
	{
		$this->cleanupDefault();
	}

	//------------------------------------------------------------------------------------- canBeNull
	public function canBeNull()
	{
		return $this->Null === "YES";
	}

	//-------------------------------------------------------------------------------- cleanupDefault
	/**
	 * Gives the default value the correct type
	 *
	 * @example remplace "0" by 0 for a numeric value (ie mysqli->fetch_object gets Default as string)
	 * @return Mysql_Column
	 */
	private function cleanupDefault()
	{
		if (isset($this->Default)) {
			if (Type::isNumeric($this->getType())) {
				$this->Default += 0;
			}
		}
		return $this;
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

	//--------------------------------------------------------------------------------- getSqlPostfix
	public function getSqlPostfix()
	{
		return $this->Extra ? " " . $this->Extra : "";
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
				return "string";
			case "enum": case "set":
				return "multitype:string";
			case "date": case "datetime": case "timestamp": case "time": case "year":
				return "Date_Time";
			case "tinyblob": case "blob": case "mediumblob": case "longblob":
				return "string";
		}
	}

	//----------------------------------------------------------------------------------------- toSql
	public function toSql()
	{
		$column_name = $this->getName();
		$type = $this->getSqlType();
		$postfix = $this->getSqlPostfix();
		$sql = "`" . $column_name. "` " . $type;
		if (!$this->canBeNull()) {
			$sql .= " NOT NULL";
		}
		if ($postfix != " auto_increment") {
			$sql .= " DEFAULT " . Sql_Value::escape($this->getDefaultValue());
		}
		$sql .= $postfix;
		if ($postfix === " auto_increment") {
			$sql .= ", PRIMARY KEY (`" . $column_name . "`)";
		}
		return $sql;
	}

}
