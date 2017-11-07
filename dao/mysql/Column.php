<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Dao\Sql;

use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Sql\Value;
use ITRocks\Framework\Tools\Date_Time;
use mysqli;

/**
 * Mysql column
 */
class Column implements Sql\Column
{
	use Column_Builder_Property;

	//-------------------------------------------------------------------------------- AUTO_INCREMENT
	const AUTO_INCREMENT = 'auto_increment';

	//-------------------------------------------------------------------------------------------- NO
	const NO = 'NO';

	//---------------------------------------------------------------------------------- STRING_TYPES
	const STRING_TYPES = ['char', 'enum', 'mediumtext', 'set', 'text', 'tinytext', 'varchar'];

	//------------------------------------------------------------------------------------------- YES
	const YES = 'YES';

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
	 * @values auto_increment
	 * @var string
	 */
	private $Extra;

	//---------------------------------------------------------------------------------------- $Field
	/**
	 * Mysql column name
	 *
	 * @var string
	 */
	private $Field;

	//------------------------------------------------------------------------------------------ $Key
	/**
	 * Is the data part of an index key ?
	 *
	 * @values PRI, MUL, UNI,
	 * @var string
	 */
	private $Key;

	//----------------------------------------------------------------------------------------- $Null
	/**
	 * Can the data be null ?
	 *
	 * @values YES, NO
	 * @var string
	 */
	private $Null;

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
	 * text types can be followed with 'CHARACTER SET utf8 COLLATE utf8_general_ci'
	 *
	 * @var string
	 */
	private $Type;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name string
	 * @param $type string
	 */
	public function __construct($name = null, $type = null)
	{
		if (isset($name)) {
			$this->Field = $name;
		}
		if (isset($type)) {
			$this->Type  = $type;
			if (!isset($this->Default)) {
				$this->Default = '';
			}
		}
		$this->cleanupDefault();
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
		$column->Null    = self::NO;
		$column->Default = null;
		$column->Extra   = self::AUTO_INCREMENT;
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
		$column->Null    = self::NO;
		$column->Default = 0;
		return $column;
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
		$column->Field   = self::propertyNameToMysql($property);
		$column->Type    = self::propertyTypeToMysql($property);
		$column->Null    = self::propertyNullToMysql($property);
		$column->Key     = self::propertyKeyToMysql($property);
		$column->Default = self::propertyDefaultToMysql($property, $column);
		$column->Extra   = '';
		return $column;
	}

	//------------------------------------------------------------------------------------ buildTable
	/**
	 * Builds a Column[] object array for a given table into a mysqli database
	 *
	 * @param $mysqli        mysqli
	 * @param $table_name    string
	 * @param $database_name string
	 * @return Column[] key is the name of the column
	 */
	public static function buildTable(mysqli $mysqli, $table_name, $database_name = null)
	{
		$database_name = isset($database_name) ? (Q . $database_name . Q) : 'DATABASE()';
		$columns       = [];
		$result        = $mysqli->query(
			'SELECT `column_name` AS `Field`,'
			. ' IFNULL(CONCAT(`column_type`, " CHARACTER SET ", `character_set_name`, " COLLATE ", `collation_name`), `column_type`) AS `Type`,'
			. ' `is_nullable` AS `Null`, `column_key` AS `Key`, `column_default` AS `Default`, `extra` AS `Extra`' . LF
			. 'FROM `information_schema`.`columns`' . LF
			. 'WHERE `table_schema` = ' . $database_name . ' AND `table_name` = ' . Q . $table_name . Q
		);
		/** @var $column Column */
		while ($column = $result->fetch_object(Column::class)) {
			if (is_null($column->Default) && !$column->canBeNull() && $column->isString()) {
				$column->Default = '';
			}
			$columns[$column->getName()] = $column;
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
		return $this->Null === self::YES;
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
				$this->Default = (strpos($this->Default, DOT) !== false)
					? floatval($this->Default)
					: intval($this->Default);
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

	//------------------------------------------------------------------------------------ getRawType
	/**
	 * Get sql raw base type, without size or any other information
	 *
	 * @return string
	 */
	public function getRawType()
	{
		$i = minSet(strpos($this->Type, '('), strpos($this->Type, SP));
		return is_null($i) ? $this->Type : substr($this->Type, 0, $i);
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
		switch ($this->getRawType()) {
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

	//-------------------------------------------------------------------------------------- isString
	/**
	 * @return boolean
	 */
	public function isString()
	{
		return in_array($this->getRawType(), self::STRING_TYPES);
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
		$type        = $this->getSqlType();
		$postfix     = $this->getSqlPostfix();
		$sql         = BQ . $column_name . BQ . SP . $type;
		if (!$this->canBeNull()) {
			$sql .= ' NOT NULL';
		}
		if (
			($postfix !== (SP . self::AUTO_INCREMENT))
			&& !in_array($type, [
				'tinyblob', 'tinytext', 'blob', 'text', 'mediumblob', 'mediumtext', 'longblob', 'longtext'
			])
		) {
			$sql .= ' DEFAULT ' . Value::escape($this->getDefaultValue());
		}
		$sql .= $postfix;
		if ($postfix === (SP . self::AUTO_INCREMENT)) {
			$sql .= ' PRIMARY KEY';
		}
		return $sql;
	}

}
