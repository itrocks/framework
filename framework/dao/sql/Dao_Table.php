<?php
namespace SAF\Framework;

interface Dao_Table
{

	//------------------------------------------------------------------------------------ getColumns
	/**
	 * @return multitype:Dao_Column
	 */
	public function getColumns();

	//------------------------------------------------------------------------------------ getIndexes
	/**
	 * @return multitype:Dao_Index
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
