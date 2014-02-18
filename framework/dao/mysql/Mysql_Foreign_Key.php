<?php
namespace SAF\Framework;

/**
 * Mysql foreign key
 */
class Mysql_Foreign_Key implements Dao_Foreign_Key
{
	use Mysql_Foreign_Key_Builder_Property;

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
	 * Builds a Mysql_Foreign_Key for a column name that links to a given class name
	 *
	 * @param $table_name  string the table name
	 * @param $column_name string the column name linking to the foreign key (with or without 'id_')
	 * @param $class_name  string the foreign class name
	 * @param $constraint  string CASCADE, NO ACTION, RESTRICT, SET NULL
	 * @return Mysql_Foreign_Key
	 */
	public static function buildLink($table_name, $column_name, $class_name, $constraint = 'CASCADE')
	{
		if (substr($column_name, 0, 3) !== 'id_') {
			$column_name = 'id_' . $column_name;
		}
		$foreign_key = new Mysql_Foreign_Key();
		$foreign_key->Constraint = $table_name . '.' . $column_name;
		$foreign_key->Fields = $column_name;
		$foreign_key->On_delete = $constraint;
		$foreign_key->On_update = $constraint;
		$foreign_key->Reference_fields = 'id';
		$foreign_key->Reference_table = Dao::storeNameOf($class_name);
		return $foreign_key;
	}

	//--------------------------------------------------------------------------------- buildProperty
	/**
	 * Builds a Mysql_Column object using a class property
	 *
	 * @param $table_name string
	 * @param $property   Reflection_Property
	 * @return Mysql_Foreign_Key
	 */
	public static function buildProperty($table_name, Reflection_Property $property)
	{
		$foreign_key = new Mysql_Foreign_Key();
		$foreign_key->Constraint       = self::propertyConstraintToMysql($table_name, $property);
		$foreign_key->Fields           = self::propertyFieldsToMysql($property);
		$foreign_key->On_delete        = self::propertyOnDeleteToMysql($property);
		$foreign_key->On_update        = self::propertyOnUpdateToMysql($property);
		$foreign_key->Reference_fields = self::propertyReferenceFieldsToMysql($property);
		$foreign_key->Reference_table  = self::propertyReferenceTableToMysql($property);
		return $foreign_key;
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
		return 'CONSTRAINT `' . $this->getConstraint() . '`'
			. ' FOREIGN KEY (`' . join('`, `', $this->getFields()) . '`)'
			. ' REFERENCES `' . $this->getReferenceTable() . '`'
			. ' (`' . join('`, `', $this->getReferenceFields()) . '`)'
			. ' ON DELETE ' . $this->getOnDelete()
			. ' ON UPDATE ' . $this->getOnUpdate();
	}

}
