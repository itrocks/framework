<?php
namespace ITRocks\Framework\Dao\Mysql\Information_Schema;

/**
 * Information schema : key column usage
 *
 * @property $column_name                   string
 * @property $constraint_catalog            string
 * @property $constraint_name               string
 * @property $constraint_schema             string
 * @property $ordinal_position              integer
 * @property $position_in_unique_constraint integer
 * @property $referenced_column_name        string
 * @property $referenced_table_name         string
 * @property $referenced_table_schema       string
 * @property $table_catalog                 string
 * @property $table_name                    string
 * @property $table_schema                  string
 */
class Key_Column_Usage
{

	//---------------------------------------------------------------------------------- $COLUMN_NAME
	/**
	 * The source table column name
	 *
	 * @var string
	 */
	protected $COLUMN_NAME;

	//--------------------------------------------------------------------------- $CONSTRAINT_CATALOG
	/**
	 * @values def
	 * @var string
	 */
	protected $CONSTRAINT_CATALOG;

	//------------------------------------------------------------------------------ $CONSTRAINT_NAME
	/**
	 * The name of the constraint is its unique identifier
	 *
	 * @var string
	 */
	protected $CONSTRAINT_NAME;

	//---------------------------------------------------------------------------- $CONSTRAINT_SCHEMA
	/**
	 * The database name which stores the constraint
	 *
	 * @var string
	 */
	protected $CONSTRAINT_SCHEMA;

	//----------------------------------------------------------------------------- $ORDINAL_POSITION
	/**
	 * @var integer
	 */
	protected $ORDINAL_POSITION;

	//---------------------------------------------------------------- $POSITION_IN_UNIQUE_CONSTRAINT
	/**
	 * @var integer
	 */
	protected $POSITION_IN_UNIQUE_CONSTRAINT;

	//----------------------------------------------------------------------- $REFERENCED_COLUMN_NAME
	/**
	 * The foreign column name (ie always id when it is generated by the framework)
	 *
	 * @values id
	 * @var string
	 */
	protected $REFERENCED_COLUMN_NAME;

	//------------------------------------------------------------------------ $REFERENCED_TABLE_NAME
	/**
	 * The foreign table name
	 *
	 * @var string
	 */
	protected $REFERENCED_TABLE_NAME;

	//---------------------------------------------------------------------- $REFERENCED_TABLE_SCHEMA
	/**
	 * The foreign database name
	 *
	 * @var string
	 */
	protected $REFERENCED_TABLE_SCHEMA;

	//-------------------------------------------------------------------------------- $TABLE_CATALOG
	/**
	 * @values def
	 * @var string
	 */
	protected $TABLE_CATALOG;

	//----------------------------------------------------------------------------------- $TABLE_NAME
	/**
	 * The source table name
	 *
	 * @var string
	 */
	protected $TABLE_NAME;

	//--------------------------------------------------------------------------------- $TABLE_SCHEMA
	/**
	 * The source database name
	 *
	 * @var string
	 */
	protected $TABLE_SCHEMA;

	//----------------------------------------------------------------------------------------- __get
	/**
	 * @param $property_name string
	 * @return mixed
	 */
	public function __get($property_name)
	{
		return $this->{strtoupper($property_name)};
	}

}
