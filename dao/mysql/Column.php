<?php
namespace SAF\Framework\Dao\Mysql;

use SAF\Framework\Dao\Sql;

use mysqli;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Reflection\Type;
use SAF\Framework\Sql\Value;
use SAF\Framework\Tools\Date_Time;

/**
 * Mysql column
 */
class Column implements Sql\Column
{
	use Column_Builder_Property;

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
	 * Value can be 'tinyint(l)', 'smallint(l)', 'mediumint(l)', 'int(l)', 'integer(l)', 'bigint(l)',
	 * 'float(p)', 'double', 'real', 'decimal(l,d)', 'numeric(l,d)', 'bit(l)',
	 * 'date', 'time', 'datetime', 'timestamp', 'year',
	 * 'char', 'binary(l)', 'varchar(l)', 'varbinary(l)',
	 * 'tinyblob', 'tinytext', 'blob', 'text', 'mediumblob', 'mediumtext', 'longblob', 'longtext',
	 * 'enum("v",...)', 'set("v",...)'
	 * where 'p' = 'precision', 'l' = 'length', 'd' = 'decimals', 'v' = 'value'
	 * numeric types can be followed with ' unsigned'
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
	 * A list of options, the most common is 'auto_increment' for primary auto-increment indexes.
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
		if (isset($type)) {
			$this->Type  = $type;
			if (!isset($this->Default)) {
				$this->Default = '';
			}
		}
		$this->cleanupDefault();
	}

	//--------------------------------------------------------------------------------- buildProperty
	/**
	 * Builds a Column object using a class property
	 *
	 * @param $property Reflection_Property
	 * @return Column
	 */
	public static function buildProperty(Reflection_Property $property)
	{
		$column = new Column();
		$column->Field   = self::propertyNameToMysql($property, $column);
		$column->Type    = self::propertyTypeToMysql($property, $column);
		$column->Null    = self::propertyNullToMysql($property, $column);
		$column->Key     = self::propertyKeyToMysql($property, $column);
		$column->Default = self::propertyDefaultToMysql($property, $column);
		$column->Extra = '';
		return $column;
	}

	//--------------------------------------------------------------------------------------- buildId
	/**
	 * Builds a Column object for a standard 'id' column
	 *
	 * @return Column
	 */
	public static function buildId()
	{
		$column = new Column('id', 'bigint(18) unsigned');
		$column->Null    = 'NO';
		$column->Default = null;
		$column->Extra   = 'auto_increment';
		return $column;
	}

	//------------------------------------------------------------------------------------- buildLink
	/**
	 * Builds a Column object for a standard 'id_*' link column
	 *
	 * @param $column_name string
	 * @return Column
	 */
	public static function buildLink($column_name)
	{
		$column = new Column($column_name, 'bigint(18) unsigned');
		$column->Null    = 'NO';
		$column->Default = 0;
		return $column;
	}

	//------------------------------------------------------------------------------------ buildTable
	/**
	 * Builds a Column[] object array for a given table into a mysqli database
	 *
	 * @param $mysqli        mysqli
	 * @param $table_name    string
	 * @param $database_name string
	 * @return Column[]
	 */
	public static function buildTable(mysqli $mysqli, $table_name, $database_name = null)
	{
		$database_name = isset($database_name) ? (DQ . $database_name . DQ) : 'DATABASE()';
		$columns = [];
		$result = $mysqli->query(
			'SELECT column_name `Field`,'
			. ' IFNULL(CONCAT(column_type, " CHARACTER SET ", character_set_name, " COLLATE ", collation_name), column_type) `Type`,'
			. ' is_nullable `Null`, column_key `Key`, column_default `Default`, extra `Extra`'
			. LF . 'FROM information_schema.columns'
			. LF . 'WHERE table_schema = ' . $database_name . ' AND table_name = ' . DQ . $table_name . DQ
		);
		/** @var $column Column */
		while ($column = $result->fetch_object(Column::class)) {
			$columns[] = $column;
		}
		$result->free();
		return $columns;
	}

	//------------------------------------------------------------------------------------- canBeNull
	/**
	 * @return bool
	 */
	public function canBeNull()
	{
		return $this->Null === 'YES';
	}

	//-------------------------------------------------------------------------------- cleanupDefault
	/**
	 * Gives the default value the correct type
	 *
	 * @example replace '0' by 0 for a numeric value (ie mysqli->fetch_object gets Default as string)
	 * @return Column
	 */
	private function cleanupDefault()
	{
		if (isset($this->Default)) {
			if ($this->getType()->isNumeric()) {
				$this->Default += 0;
			}
			elseif ($this->getType()->isString()) {
				$this->Default = strval($this->Default);
			}
		}
		return $this;
	}

	//----------------------------------------------------------------------------------------- equiv
	/**
	 * Returns true if the column is an equivalent of the other column
	 *
	 * @param $column Column
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
		return $this->Extra ? (SP . $this->Extra) : '';
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
		$i = strpos($this->Type, '(');
		$type = ($i === false) ? $this->Type : substr($this->Type, 0, $i);
		switch ($type) {
			case 'decimal': case 'float': case 'double':
				return new Type(Type::FLOAT);
			case 'tinyint': case 'smallint': case 'mediumint': case 'int': case 'bigint':
				return new Type(Type::INTEGER);
			case 'enum': case 'set':
				return (new Type(Type::STRING))->multiple();
			case 'date': case 'datetime': case 'timestamp': case 'time': case 'year':
				return new Type(Date_Time::class);
			default:
				return new Type(Type::STRING);
		}
	}

	//---------------------------------------------------------------------------------------- hasKey
	/**
	 * @return boolean
	 */
	public function hasKey()
	{
		return !empty($this->Key);
	}

	//------------------------------------------------------------------------------- setDefaultValue
	/**
	 * @param $default string
	 */
	public function setDefaultValue($default)
	{
		$this->Default = $default;
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
		$sql = BQ . $column_name . BQ . SP . $type;
		if (!$this->canBeNull()) {
			$sql .= ' NOT NULL';
		}
		if (
			($postfix != ' auto_increment')
			&& !in_array($type, [
				'tinyblob', 'tinytext', 'blob', 'text', 'mediumblob', 'mediumtext', 'longblob', 'longtext'
			])
		) {
			$sql .= ' DEFAULT ' . Value::escape($this->getDefaultValue());
		}
		$sql .= $postfix;
		if ($postfix === ' auto_increment') {
			$sql .= ' PRIMARY KEY';
		}
		return $sql;
	}

}
