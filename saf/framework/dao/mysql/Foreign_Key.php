<?php
namespace SAF\Framework\Dao\Mysql;

use SAF\Framework\Dao\Sql;

use mysqli;
use SAF\Framework\Dao;
use SAF\Framework\Reflection\Reflection_Property;

/**
 * Mysql foreign key
 */
class Foreign_Key implements Sql\Foreign_Key
{
	use Foreign_Key_Builder_Property;

	/**
	 * @var string
	 */
	private $Constraint;

	/**
	 * @var string
	 */
	private $Fields;

	/**
	 * @var string
	 * @values CASCADE, NO ACTION, RESTRICT, SET NULL
	 */
	private $On_delete = 'RESTRICT';

	/**
	 * @var string
	 * @values CASCADE, NO ACTION, RESTRICT, SET NULL
	 */
	private $On_update = 'RESTRICT';

	/**
	 * @var string
	 */
	private $Reference_fields;

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
	 * @param $constraint  string CASCADE, NO ACTION, RESTRICT, SET NULL
	 * @return Foreign_Key
	 */
	public static function buildLink($table_name, $column_name, $class_name, $constraint = 'CASCADE')
	{
		if (substr($column_name, 0, 3) !== 'id_') {
			$column_name = 'id_' . $column_name;
		}
		$foreign_key = new Foreign_Key();
		$foreign_key->Constraint = $table_name . DOT . $column_name;
		$foreign_key->Fields = $column_name;
		$foreign_key->On_delete = $constraint;
		$foreign_key->On_update = $constraint;
		$foreign_key->Reference_fields = 'id';
		$foreign_key->Reference_table = Dao::storeNameOf($class_name);
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
		$foreign_key->Reference_fields = self::propertyReferenceFieldsToMysql($property);
		$foreign_key->Reference_table  = self::propertyReferenceTableToMysql($property);
		return $foreign_key;
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
		$database_name = isset($database_name) ? (DQ . $database_name . DQ) : 'DATABASE()';
		$foreign_keys = [];
		$result = $mysqli->query(
			'SELECT constraint_name `Constraint`,'
			. ' RIGHT(constraint_name, LENGTH(constraint_name) - LOCATE(".", constraint_name)) `Fields`,'
			. ' update_rule `On_update`, delete_rule `On_delete`,'
			. ' referenced_table_name `Reference_table`, "id" `Reference_fields`'
			. LF . 'FROM information_schema.referential_constraints'
			. LF . 'WHERE constraint_schema = ' . $database_name
			. ' AND table_name = ' . DQ . $table_name . DQ
		);
		while ($foreign_key = $result->fetch_object(Foreign_Key::class)) {
			$foreign_keys[] = $foreign_key;
		}
		$result->free();
		return $foreign_keys;
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
		$database_name = isset($database_name) ? (DQ . $database_name . DQ) : 'DATABASE()';
		$foreign_keys = [];
		$result = $mysqli->query(
			'SELECT constraint_name `Constraint`,'
			. ' RIGHT(constraint_name, LENGTH(constraint_name) - LOCATE(".", constraint_name)) `Fields`,'
			. ' update_rule `On_update`, delete_rule `On_delete`,'
			. ' referenced_table_name `Reference_table`, "id" `Reference_fields`'
			. LF . 'FROM information_schema.referential_constraints'
			. LF . 'WHERE constraint_schema = ' . $database_name
			. ' AND referenced_table_name = ' . DQ . $table_name . DQ
		);
		while ($foreign_key = $result->fetch_object(Foreign_Key::class)) {
			$foreign_keys[] = $foreign_key;
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
