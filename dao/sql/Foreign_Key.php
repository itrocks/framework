<?php
namespace ITRocks\Framework\Dao\Sql;

/**
 * A common interface for Dao foreign key object representation
 */
interface Foreign_Key
{

	//--------------------------------------------------------------------------------------- CASCADE
	const CASCADE = 'CASCADE';

	//-------------------------------------------------------------------------------------- RESTRICT
	const RESTRICT = 'RESTRICT';

	//-------------------------------------------------------------------------------------- SET_NULL
	const SET_NULL = 'SET NULL';

	//--------------------------------------------------------------------------------- getConstraint
	/**
	 * @return string
	 */
	public function getConstraint() : string;

	//------------------------------------------------------------------------------------- getFields
	/**
	 * @return string[]
	 */
	public function getFields() : array;

	//----------------------------------------------------------------------------------- getOnDelete
	/**
	 * @return string
	 */
	public function getOnDelete() : string;

	//----------------------------------------------------------------------------------- getOnUpdate
	/**
	 * @return string
	 */
	public function getOnUpdate() : string;

	//---------------------------------------------------------------------------- getReferenceFields
	/**
	 * @return string[]
	 */
	public function getReferenceFields() : array;

	//----------------------------------------------------------------------------- getReferenceTable
	/**
	 * @return string
	 */
	public function getReferenceTable() : string;

	//------------------------------------------------------------------------------------- toDropSql
	/**
	 * @return string
	 */
	public function toDropSql() : string;

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * @return string
	 */
	public function toSql() : string;

}
