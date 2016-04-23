<?php
namespace SAF\Framework\Sql;

use SAF\Framework\Dao;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Tools\Names;

/**
 * Manages link tables for map properties
 */
class Link_Table
{

	//------------------------------------------------------------------------------- $foreign_column
	/**
	 * @var string
	 */
	private $foreign_column;

	//-------------------------------------------------------------------------------- $master_column
	/**
	 * @var string
	 */
	private $master_column;

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	private $property;

	//---------------------------------------------------------------------------------------- $table
	/**
	 * @var string
	 */
	private $table;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * $property @link annotation must be a Map to manage link tables
	 *
	 * @param Reflection_Property $property
	 */
	function __construct(Reflection_Property $property)
	{
		$this->property = $property;
	}

	//--------------------------------------------------------------------- applyTableNameDefinitions
	/**
	 * Replace string definitions by their values
	 *
	 * @param $table string table name
	 * @return mixed
	 */
	private function applyTableNameDefinitions($table)
	{
		if (strpos($table, '{') !== false) {
			$master_table  = Dao::storeNameOf($this->property->class);
			$foreign_table = Dao::storeNameOf($this->property->getType()->getElementTypeAsString());
			$definitions   = [
				'{default}' => $this->defaultStoreName($master_table, $foreign_table),
				'{master}'  => $master_table,
				'{foreign}' => $foreign_table
			];
			foreach ($definitions as $def => $value) {
				$table = str_replace($def, $value, $table);
			}
			return $table;
		}
		return $table;
	}

	//------------------------------------------------------------------------------ defaultStoreName
	/**
	 * Construct table link name between two tables
	 *
	 * @param $master_table   string
	 * @param $foreign_table  string
	 * @return string
	 */
	private function defaultStoreName($master_table, $foreign_table)
	{
		return ($master_table < $foreign_table)
			? ($master_table . '_' . $foreign_table)
			: ($foreign_table . '_' . $master_table);
	}

	//--------------------------------------------------------------------------------- foreignColumn
	/**
	 * @return string
	 */
	function foreignColumn()
	{
		if (!isset($this->foreign_column)) {
			$this->foreign_column = 'id_' . Names::setToSingle(
				$this->property->getAnnotation('foreignlink')->value
			);
		}
		return $this->foreign_column;
	}

	//---------------------------------------------------------------------------------- masterColumn
	/**
	 * @return string
	 */
	function masterColumn()
	{
		if (!isset($this->master_column)) {
			$this->master_column = 'id_' . Names::setToSingle(
				$this->property->getAnnotation('foreign')->value
			);
		}
		return $this->master_column;
	}

	//----------------------------------------------------------------------------------------- table
	/**
	 * @return string
	 */
	function table()
	{
		if (!isset($this->table)) {
			$table = $this->property->getAnnotation('set_store_name')->value;
			if ($table && is_string($table)) {
				$table       = $this->applyTableNameDefinitions($table);
				$this->table = strtolower($table);
			}
			else {
				$master_table  = Dao::storeNameOf($this->property->class);
				$foreign_table = Dao::storeNameOf($this->property->getType()->getElementTypeAsString());
				$this->table = $this->defaultStoreName($master_table, $foreign_table);
			}
		}
		return $this->table;
	}

}
