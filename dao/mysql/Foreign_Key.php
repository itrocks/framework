<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Sql;
use ITRocks\Framework\Reflection\Reflection_Property;
use mysqli;

/**
 * Mysql foreign key
 */
class Foreign_Key implements Sql\Foreign_Key
{
	use Foreign_Key_Builder_Property;

	//----------------------------------------------------------------------------------- $Constraint
	/**
	 * @var string
	 */
	private $Constraint;

	//--------------------------------------------------------------------------------------- $Fields
	/**
	 * @var string
	 */
	private $Fields;

	//------------------------------------------------------------------------------------ $On_delete
	/**
	 * @values CASCADE, NO ACTION, RESTRICT, SET NULL
	 * @var string
	 */
	private $On_delete = 'RESTRICT';

	//------------------------------------------------------------------------------------ $On_update
	/**
	 * @values CASCADE, NO ACTION, RESTRICT, SET NULL
	 * @var string
	 */
	private $On_update = 'RESTRICT';

	//----------------------------------------------------------------------------- $Reference_fields
	/**
	 * @var string
	 */
	private $Reference_fields;

	//------------------------------------------------------------------------------ $Reference_table
	/**
	 * @var string
	 */
	private $Reference_table;

	//------------------------------------------------------------------------------------- buildLink
	/**
	 * Builds a Foreign_Key for a column name that links to a given class name
	 *
	 * @param $table_name  string the table name
	 * @param $column_name string the column name linking to the foreign key (with or without 'id_')
	 * @param $class_name  string the foreign class name
	 * @param $on          string CASCADE, NO ACTION, RESTRICT, SET NULL
	 * @return Foreign_Key
	 */
	public static function buildLink($table_name, $column_name, $class_name, $on = self::CASCADE)
	{
		if (substr($column_name, 0, 3) !== 'id_') {
			$column_name = 'id_' . $column_name;
		}
		$constraint = $table_name . DOT . $column_name;
		if (strlen($constraint) > 64) {
			$constraint = md5($table_name) . md5($column_name);
		}

		$foreign_key                   = new Foreign_Key();
		$foreign_key->Constraint       = $constraint;
		$foreign_key->Fields           = $column_name;
		$foreign_key->On_delete        = $on;
		$foreign_key->On_update        = $on;
		$foreign_key->Reference_fields = 'id';
		$foreign_key->Reference_table  = Dao::storeNameOf($class_name);
		return $foreign_key;
	}

	//--------------------------------------------------------------------------------- buildProperty
	/**
	 * Builds a Foreign_Key object using a class property
	 *
	 * @param $table_name string
	 * @param $property   Reflection_Property
	 * @return Foreign_Key
	 */
	public static function buildProperty($table_name, Reflection_Property $property)
	{
		$foreign_key = new Foreign_Key();
		$foreign_key->Constraint       = self::propertyConstraintToMysql($table_name, $property);
		$foreign_key->Fields           = self::propertyFieldsToMysql($property);
		$foreign_key->On_delete        = self::propertyOnDeleteToMysql($property);
		$foreign_key->On_update        = self::propertyOnUpdateToMysql($property);
		$foreign_key->Reference_fields = self::propertyReferenceFieldsToMysql();
		$foreign_key->Reference_table  = self::propertyReferenceTableToMysql($property);
		return $foreign_key;
	}

	//------------------------------------------------------------------------------- buildReferences
	/**
	 * Builds a Foreign_Key[] object array using database information for a given
	 * referenced table
	 *
	 * @param $mysqli        mysqli
	 * @param $table_name    string
	 * @param $database_name string
	 * @return Foreign_Key[]
	 */
	public static function buildReferences(mysqli $mysqli, $table_name, $database_name = null)
	{
		return static::foreignKeysOf($mysqli, 'referenced_table_name', $table_name, $database_name);
	}

	//------------------------------------------------------------------------------------ buildTable
	/**
	 * Builds a Foreign_Key[] object array using database information for a given table
	 *
	 * @param $mysqli        mysqli
	 * @param $table_name    string
	 * @param $database_name string
	 * @return Foreign_Key[]
	 */
	public static function buildTable(mysqli $mysqli, $table_name, $database_name = null)
	{
		return static::foreignKeysOf($mysqli, 'table_name', $table_name, $database_name);
	}

	//---------------------------------------------------------------------------------- diffCombined
	/**
	 * @param $foreign_key Foreign_Key
	 * @return array
	 */
	public function diffCombined(Foreign_Key $foreign_key)
	{
		return arrayDiffCombined(get_object_vars($this), get_object_vars($foreign_key), true);
	}

	//----------------------------------------------------------------------------------------- equiv
	/**
	 * @param $foreign_key Foreign_Key
	 * @return boolean
	 */
	public function equiv(Foreign_Key $foreign_key)
	{
		return ($this->Constraint === $foreign_key->Constraint)
			&& ($this->Fields === $foreign_key->Fields)
			&& ($this->On_delete === $foreign_key->On_delete)
			&& ($this->On_update === $foreign_key->On_update)
			&& ($this->Reference_fields === $foreign_key->Reference_fields)
			&& ($this->Reference_table === $foreign_key->Reference_table);
	}

	//--------------------------------------------------------------------------------- foreignKeysOf
	/**
	 * @param $mysqli            mysqli
	 * @param $table_name_column string @values referenced_table_name, table_name
	 * @param $table_name        string @example users
	 * @param $database_name     string|null if null, will be current database
	 * @return Foreign_Key[]
	 */
	protected static function foreignKeysOf(
		mysqli $mysqli, $table_name_column, $table_name, $database_name = null
	) {
		$database_name = isset($database_name) ? (Q . $database_name . Q) : 'DATABASE()';

		/** @var $foreign_keys Foreign_Key[] */
		$foreign_keys = [];

		// Why two queries ? A single query with a join would be very slower

		// Constraint, Fields, Reference_fields, Reference_table
		$result = $mysqli->query("
			SELECT `constraint_name`   `Constraint`,
				`column_name`            `Fields`,
				`referenced_column_name` `Reference_fields`,
				`referenced_table_name`  `Reference_table`
			FROM `information_schema`.`key_column_usage`
			WHERE `constraint_schema` = $database_name
			AND `$table_name_column` = '$table_name'
			AND `referenced_column_name` IS NOT NULL
			AND `referenced_table_name` IS NOT NULL
		");
		while ($foreign_key = $result->fetch_object(Foreign_Key::class)) {
			$foreign_keys[$foreign_key->Constraint] = $foreign_key;
		}
		$result->free();

		// On_delete, On_update
		$result = $mysqli->query("
			SELECT `constraint_name` `Constraint`, `delete_rule` `On_delete`, `update_rule` `On_update`
			FROM `information_schema`.`referential_constraints`
			WHERE `constraint_schema` = $database_name
			AND `$table_name_column` = '$table_name'
		");
		while ($foreign_key = $result->fetch_object(Foreign_Key::class)) {
			$foreign_keys[$foreign_key->Constraint]->On_delete = $foreign_key->On_delete;
			$foreign_keys[$foreign_key->Constraint]->On_update = $foreign_key->On_update;
		}
		$result->free();

		return $foreign_keys;
	}

	//--------------------------------------------------------------------------------- getConstraint
	/**
	 * @return string
	 */
	public function getConstraint()
	{
		return $this->Constraint;
	}

	//------------------------------------------------------------------------------------- getFields
	/**
	 * @return string[]
	 */
	public function getFields()
	{
		return explode(',', $this->Fields);
	}

	//----------------------------------------------------------------------------------- getOnDelete
	/**
	 * @return string
	 */
	public function getOnDelete()
	{
		return $this->On_delete;
	}

	//----------------------------------------------------------------------------------- getOnUpdate
	/**
	 * @return string
	 */
	public function getOnUpdate()
	{
		return $this->On_update;
	}

	//---------------------------------------------------------------------------- getReferenceFields
	/**
	 * @return string[]
	 */
	public function getReferenceFields()
	{
		return explode(',', $this->Reference_fields);
	}

	//----------------------------------------------------------------------------- getReferenceTable
	/**
	 * @return string
	 */
	public function getReferenceTable()
	{
		return $this->Reference_table;
	}

	//------------------------------------------------------------------------------------- toDropSql
	/**
	 * @return string
	 */
	public function toDropSql()
	{
		return 'DROP FOREIGN KEY ' . BQ . $this->getConstraint() . BQ;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * @return string
	 */
	public function toSql()
	{
		return 'CONSTRAINT ' . BQ . $this->getConstraint() . BQ
			. ' FOREIGN KEY (' . BQ . join('`, `', $this->getFields()) . BQ . ')'
			. ' REFERENCES ' . BQ . $this->getReferenceTable() . BQ
			. ' (' . BQ . join(BQ . ', ' . BQ, $this->getReferenceFields()) . BQ . ')'
			. ' ON DELETE ' . $this->getOnDelete()
			. ' ON UPDATE ' . $this->getOnUpdate();
	}

}
