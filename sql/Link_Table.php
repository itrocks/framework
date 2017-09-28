<?php
namespace ITRocks\Framework\Sql;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Annotation\Property\Foreign_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Names;

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
			$master_table  = Dao::storeNameOf($this->property->final_class);
			$foreign_table = Dao::storeNameOf($this->property->getType()->getElementTypeAsString());
			$definitions   = [
				'{master}'  => $master_table,
				'{foreign}' => $foreign_table
			];
			if (strpos($table, '{default}') !== false) {
				$definitions['{default}'] = $this->defaultStoreName($master_table, $foreign_table);
			}
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
		list($left, $right) = ($master_table < $foreign_table)
			? [$master_table, $foreign_table]
			: [$foreign_table, $master_table];
		$left  = explode('_', $left);
		$right = explode('_', $right);
		$last  = min(count($left), count($right)) - 1;
		$skip = 0;
		while (($skip < $last) && ($right[$skip] === $left[$skip])) {
			$skip ++;
		}
		return join('_', $left) . '_' . join('_', array_slice($right, $skip));
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
				Foreign_Annotation::of($this->property)->value
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
