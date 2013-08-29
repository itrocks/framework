<?php
namespace SAF\Framework;

/**
 * A common interface for Dao foreign key object representation
 */
interface Dao_Foreign_Key
{

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

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * @return string
	 */
	public function toSql();

}
