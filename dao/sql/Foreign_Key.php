<?php
namespace SAF\Framework\Dao\Sql;

/**
 * A common interface for Dao foreign key object representation
 */
interface Foreign_Key
{

	//--------------------------------------------------------------------------------------- CASCADE
	const CASCADE = 'CASCADE';

	//-------------------------------------------------------------------------------------- RESTRICT
	const RESTRICT = 'RESTRICT';

	//--------------------------------------------------------------------------------- getConstraint
	/**
	 * @return string
	 */
	public function getConstraint();

	//------------------------------------------------------------------------------------- getFields
	/**
	 * @return string[]
	 */
	public function getFields();

	//----------------------------------------------------------------------------------- getOnDelete
	/**
	 * @return string
	 */
	public function getOnDelete();

	//----------------------------------------------------------------------------------- getOnUpdate
	/**
	 * @return string
	 */
	public function getOnUpdate();

	//---------------------------------------------------------------------------- getReferenceFields
	/**
	 * @return string[]
	 */
	public function getReferenceFields();

	//----------------------------------------------------------------------------- getReferenceTable
	/**
	 * @return string
	 */
	public function getReferenceTable();

	//------------------------------------------------------------------------------------- toDropSql
	/**
	 * @return string
	 */
	public function toDropSql();

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * @return string
	 */
	public function toSql();

}
