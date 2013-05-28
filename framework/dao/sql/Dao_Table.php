<?php
namespace SAF\Framework;

/**
 * A common class for Dao table object representation
 */
interface Dao_Table
{

	//------------------------------------------------------------------------------------ getColumns
	/**
	 * @return Dao_Column[]
	 */
	public function getColumns();

	//------------------------------------------------------------------------------------ getIndexes
	/**
	 * @return Dao_Index[]
	 */
	public function getIndexes();

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName();

	//--------------------------------------------------------------------------------- getSqlPostfix
	/**
	 * @return string
	 */
	public function getSqlPostfix();

}
