<?php
namespace ITRocks\Framework\Dao\Sql;

use ITRocks\Framework\Tools\Field;

/**
 * A common interface for Dao column object representation
 */
interface Column extends Field
{

	//------------------------------------------------------------------------------------- canBeNull
	/**
	 * @return boolean
	 */
	public function canBeNull() : bool;

	//----------------------------------------------------------------------------------------- equiv
	/**
	 * Returns true if the column is an equivalent of the other column
	 *
	 * @param $column Column
	 */
	public function equiv(Column $column);

	//------------------------------------------------------------------------------- getDefaultValue
	/**
	 * Gets the default value of a field
	 *
	 * @return mixed
	 */
	public function getDefaultValue() : mixed;

	//--------------------------------------------------------------------------------- getSqlPostfix
	/**
	 * Gets the SQL postfix for the column
	 *
	 * @return string
	 */
	public function getSqlPostfix() : string;

	//------------------------------------------------------------------------------------ getSqlType
	/**
	 * Gets the SQL version of the type
	 *
	 * @return string
	 */
	public function getSqlType() : string;

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Gets the SQL version of the column definition
	 *
	 * @param $primary_key boolean if false, no PRIMARY KEY will be added to columns
	 * @return string
	 */
	public function toSql(bool $primary_key = true) : string;

}
