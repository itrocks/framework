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

	//----------------------------------------------------------------------------------- PRIMARY_KEY
	const PRIMARY_KEY = 'PRI';

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
	 * Cannot be null : no extra = ''
	 *
	 * @values auto_increment
	 * @var string
	 */
	private $Extra = '';

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
	 * Cannot be null : no key = ''
	 *
	 * @values PRI, MUL, UNI,
	 * @var string
	 */
	private $Key = '';

	//----------------------------------------------------------------------------------------- $Null
	/**
	 * Can the data be null ?
	 *
	 * Cannot be null : not null = 'NO'
	 *
	 * @values YES, NO
	 * @var string
	 */
	private $Null = 'NO';

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

	//----------------------------------------------------------------------------- alwaysNullDefault
	/**
	 * true if the column type has a DEFAULT value that is always NULL into MySQL, even if NOT NULL
	 *
	 * @return boolean
	 */
	public function alwaysNullDefault()
	{
		return in_array(
			lParse($this->Type, SP),
			['blob', 'longblob', 'longtext', 'mediumblob', 'mediumtext', 'text', 'tinyblob', 'tinytext']
		);
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
		$column->Default = null;
		$column->Extra   = self::AUTO_INCREMENT;
		$column->Key     = self::PRIMARY_KEY;
		$column->Null    = self::NO;
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
		$column->Default = 0;
		$column->Null    = self::NO;
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
		// instructions order matters : do not change it
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
		while ($column = $result->fetch_object(Column::class)) {
			if (
				is_null($column->Default)
				&& $column->isString()
				&& !$column->canBeNull()
				&& !$column->alwaysNullDefault()
			) {
				$column->Default = '';
			}
			$columns[$column->getName()] = $column;
		}
		$result->free();
		return $columns;
	}

	//------------------------------------------------------------------------------------- canBeNull
	/**
	 * @return boolean
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

	//---------------------------------------------------------------------------------- diffCombined
	/**
	 * @param $column Column
	 * @return array
	 */
	public function diffCombined(Column $column)
	{
		return arrayDiffCombined(get_object_vars($this), get_object_vars($column), true);
	}

	//----------------------------------------------------------------------------------------- equiv
	/**
	 * Returns true if the column is an equivalent of the other column
	 *
	 * @param $column Column|Sql\Column
	 * @return boolean
	 */
	public function equiv(Sql\Column $column)
	{
		if (is_numeric($this->Default) && is_numeric($column->Default)) {
			$this_default   = strval($this->Default);
			$column_default = strval($column->Default);
		}
		else {
			$this_default   = $this->Default;
			$column_default = $column->Default;
		}
		return ($this_default === $column_default)
			&& ($this->Extra === $column->Extra)
			&& ($this->Field === $column->Field)
			&& ($this->Null  === $column->Null)
			&& ($this->Type  === $column->Type);
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

	//---------------------------------------------------------------------------------- isPrimaryKey
	/**
	 * @return boolean
	 */
	public function isPrimaryKey()
	{
		return ($this->Key === self::PRIMARY_KEY);
	}

	//-------------------------------------------------------------------------------------- isString
	/**
	 * @return boolean
	 */
	public function isString()
	{
		return in_array($this->getRawType(), self::STRING_TYPES);
	}

	//--------------------------------------------------------------------------------------- reduces
	/**
	 * @param $old_column Sql\Column
	 * @return boolean true if $this type reduces data size from $old_column and may break it
	 */
	public function reduces(/** @noinspection PhpUnusedParameterInspection */ Sql\Column $old_column)
	{
		// TODO reduce calculation
		return false;
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
	 * @param $primary_key boolean if false, no PRIMARY KEY will be added to auto_increment columns
	 * @return string
	 */
	public function toSql($primary_key = true)
	{
		$column_name = $this->getName();
		$type        = $this->getSqlType();
		$postfix     = $this->getSqlPostfix();
		$sql         = BQ . $column_name . BQ . SP . $type;
		if (!$this->canBeNull()) {
			$sql .= ' NOT NULL';
		}
		if (($postfix !== (SP . self::AUTO_INCREMENT)) && !$this->alwaysNullDefault()) {
			$sql .= ' DEFAULT ' . Value::escape($this->getDefaultValue());
		}
		$sql .= $postfix;
		if ($primary_key && ($postfix === (SP . self::AUTO_INCREMENT))) {
			$sql .= ' PRIMARY KEY';
		}
		return $sql;
	}

}
