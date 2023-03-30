<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Sql;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
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
	 */
	private mixed $Default;

	//---------------------------------------------------------------------------------------- $Extra
	/**
	 * Extra options to the column
	 * A list of options, the most common is 'auto_increment' for primary auto-increment indexes.
	 *
	 * Cannot be null : no extra = ''
	 */
	#[Values(self::AUTO_INCREMENT, '')]
	private string $Extra = '';

	//---------------------------------------------------------------------------------------- $Field
	/** Mysql column name */
	private string $Field;

	//------------------------------------------------------------------------------------------ $Key
	/**
	 * Is the data part of an index key ?
	 *
	 * Cannot be null : no key = ''
	 */
	#[Values('PRI, MUL, UNI,')]
	private string $Key = '';

	//----------------------------------------------------------------------------------------- $Null
	/**
	 * Can the data be null ?
	 *
	 * Cannot be null : not null = self::NO
	 */
	#[Values(self::NO, self::YES)]
	private string $Null = self::NO;

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
	 * text types can be followed with 'CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci'
	 */
	private string $Type;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name string|null
	 * @param $type string|null
	 */
	public function __construct(string $name = null, string $type = null)
	{
		if (isset($name)) {
			$this->Field = $name;
		}
		if (isset($type)) {
			$this->Type = $type;
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
	public function alwaysNullDefault() : bool
	{
		return in_array(
			lParse($this->Type, SP),
			['blob', 'longblob', 'longtext', 'mediumblob', 'mediumtext', 'text', 'tinyblob', 'tinytext'],
			true
		);
	}

	//---------------------------------------------------------------------------- buildClassProperty
	/**
	 * Builds a Column object that contains the name of the class for an abstract object property
	 *
	 * @param $property Reflection_Property
	 * @return Column
	 */
	public static function buildClassProperty(Reflection_Property $property) : Column
	{
		$column = new Column();
		// instructions order may matters : do not change it
		$column->Field   = self::propertyNameToMysql($property) . '_class';
		$column->Type    = self::sqlTextColumn(255);
		$column->Null    = self::NO;
		$column->Default = '';
		$column->Extra   = '';
		return $column;
	}

	//--------------------------------------------------------------------------------------- buildId
	/**
	 * Builds a Column object for a standard 'id' column
	 *
	 * @return Column
	 */
	public static function buildId() : Column
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
	public static function buildLink(string $column_name) : Column
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
	public static function buildProperty(Reflection_Property $property) : Column
	{
		$column = new Column();
		// instructions order matters : do not change it
		$column->Extra   = '';
		$column->Field   = self::propertyNameToMysql($property);
		$column->Type    = self::propertyTypeToMysql($property);
		$column->Null    = self::propertyNullToMysql($property);
		$column->Key     = self::propertyKeyToMysql($property);
		$column->Default = self::propertyDefaultToMysql($property, $column);
		return $column;
	}

	//------------------------------------------------------------------------------------ buildTable
	/**
	 * Builds a Column[] object array for a given table into a mysqli database
	 *
	 * @param $mysqli        mysqli
	 * @param $table_name    string
	 * @param $database_name string|null
	 * @return Column[] key is the name of the column
	 */
	public static function buildTable(
		mysqli $mysqli, string $table_name, string $database_name = null
	) : array
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
	public function canBeNull() : bool
	{
		return $this->Null === self::YES;
	}

	//-------------------------------------------------------------------------------- cleanupDefault
	/**
	 * Gives the default value the correct type
	 *
	 * @example replace '0' by 0 for a numeric value (ie mysqli->fetch_object gets Default as string)
	 * @return $this
	 */
	private function cleanupDefault() : static
	{
		if (isset($this->Default) && ($this->Null === 'NO')) {
			if ($this->getType()->isFloat()) {
				$this->Default = floatval($this->Default);
			}
			elseif ($this->getType()->isInteger()) {
				$this->Default = intval($this->Default);
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
	public function diffCombined(Column $column) : array
	{
		return arrayDiffCombined(get_object_vars($this), get_object_vars($column), true);
	}

	//----------------------------------------------------------------------------------------- equiv
	/**
	 * Returns true if the column is an equivalent of the other column
	 *
	 * @param $column Sql\Column
	 * @return boolean
	 */
	public function equiv(Sql\Column $column) : bool
	{
		$column_default = strval(
			is_object($this->Default)
				? (Dao::getObjectIdentifier($this->Default) ?: strval($this->Default))
				: $this->Default

		);
		$this_default = strval(
			is_object($this->Default)
				? (Dao::getObjectIdentifier($this->Default) ?: strval($this->Default))
				: $this->Default
		);
		$column_extra = str_replace('DEFAULT_GENERATED', '', $column->Extra);
		$this_extra   = str_replace('DEFAULT_GENERATED', '', $this->Extra);
		$type1        = $column->Type;
		$type2        = $this->Type;
		if ($type1 !== $type2) {
			if (str_contains($type1, '(') && !str_contains($type2, '(')) {
				$type1 = lParse($type1, '(') . rParse($type1, ')');
			}
			elseif (str_contains($type2, '(') && !str_contains($type1, '(')) {
				$type2 = lParse($type2, '(') . rParse($type2, ')');
			}
		}
		return ($this_default === $column_default)
			&& ($this_extra     === $column_extra)
			&& ($this->Field    === $column->Field)
			&& ($this->Null     === $column->Null)
			&& ($type1          === $type2);
	}

	//------------------------------------------------------------------------------- getDefaultValue
	/**
	 * @return mixed
	 */
	public function getDefaultValue() : mixed
	{
		return $this->Default;
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName() : string
	{
		return $this->Field;
	}

	//------------------------------------------------------------------------------------ getRawType
	/**
	 * Get sql raw base type, without size or any other information
	 *
	 * @return string
	 */
	public function getRawType() : string
	{
		$i = minSet(strpos($this->Type, '('), strpos($this->Type, SP));
		return is_null($i) ? $this->Type : substr($this->Type, 0, $i);
	}

	//--------------------------------------------------------------------------------- getSqlPostfix
	/**
	 * @return string
	 */
	public function getSqlPostfix() : string
	{
		return $this->Extra ? (SP . $this->Extra) : '';
	}

	//------------------------------------------------------------------------------------ getSqlType
	/**
	 * @return string
	 */
	public function getSqlType() : string
	{
		return $this->Type;
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * @return Type
	 */
	public function getType() : Type
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
		}
		return new Type(Type::STRING);
	}

	//---------------------------------------------------------------------------------------- hasKey
	/**
	 * @return boolean
	 */
	public function hasKey() : bool
	{
		return !empty($this->Key);
	}

	//---------------------------------------------------------------------------------- isPrimaryKey
	/**
	 * @return boolean
	 */
	public function isPrimaryKey() : bool
	{
		return ($this->Key === self::PRIMARY_KEY);
	}

	//-------------------------------------------------------------------------------------- isString
	/**
	 * @return boolean
	 */
	public function isString() : bool
	{
		return in_array($this->getRawType(), self::STRING_TYPES, true);
	}

	//--------------------------------------------------------------------------------------- reduces
	/**
	 * @param $old_column Sql\Column
	 * @return boolean true if $this type reduces data size from $old_column and may break it
	 */
	public function reduces(/** @noinspection PhpUnusedParameterInspection */ Sql\Column $old_column)
		: bool
	{
		// TODO reduce calculation
		return false;
	}

	//------------------------------------------------------------------------------- setDefaultValue
	/**
	 * @param $default mixed
	 */
	public function setDefaultValue(mixed $default) : void
	{
		$this->Default = $default;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * @param $primary_key boolean if false, no PRIMARY KEY will be added to auto_increment columns
	 * @return string
	 */
	public function toSql(bool $primary_key = true) : string
	{
		$column_name = $this->getName();
		$type        = $this->getSqlType();
		$postfix     = $this->getSqlPostfix();
		$sql         = BQ . $column_name . BQ . SP . $type;
		if (!$this->canBeNull()) {
			$sql .= ' NOT NULL';
		}
		if (($postfix !== (SP . self::AUTO_INCREMENT)) && !$this->alwaysNullDefault()) {
			$default_value = $this->getDefaultValue();
			$default = (($type === 'datetime') && ($default_value === 'CURRENT_TIMESTAMP'))
				? $default_value
				: Value::escape($default_value);
			if (is_numeric($default)) {
				$default = Q . $default . Q;
			}
			$sql .= ' DEFAULT ' . $default;
		}
		$sql .= $postfix;
		if ($primary_key && ($postfix === (SP . self::AUTO_INCREMENT))) {
			$sql .= ' PRIMARY KEY';
		}
		return $sql;
	}

}
