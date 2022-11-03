<?php
namespace ITRocks\Framework\Sql;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Annotation\Property\Foreign_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Foreignlink_Annotation;
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
	private string $foreign_column = '';

	//-------------------------------------------------------------------------------- $master_column
	/**
	 * @var string
	 */
	private string $master_column = '';

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	private Reflection_Property $property;

	//---------------------------------------------------------------------------------------- $table
	/**
	 * @var string
	 */
	private string $table = '';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * $property @link annotation must be a Map to manage link tables
	 *
	 * @param $property Reflection_Property
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
	 * @return string
	 */
	private function applyTableNameDefinitions(string $table) : string
	{
		if (str_contains($table, '{')) {
			$master_table  = Dao::storeNameOf($this->property->final_class);
			$foreign_table = Dao::storeNameOf($this->property->getType()->getElementTypeAsString());
			$definitions   = [
				'{master}'  => $master_table,
				'{foreign}' => $foreign_table
			];
			if (str_contains($table, '{default}')) {
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
	private function defaultStoreName(string $master_table, string $foreign_table) : string
	{
		[$left, $right] = ($master_table < $foreign_table)
			? [$master_table, $foreign_table]
			: [$foreign_table, $master_table];
		$left  = explode('_', $left);
		$right = explode('_', $right);
		$last  = min(count($left), count($right)) - 1;
		$skip  = 0;
		while (($skip < $last) && ($right[$skip] === $left[$skip])) {
			$skip ++;
		}
		return join('_', $left) . '_' . join('_', array_slice($right, $skip));
	}

	//--------------------------------------------------------------------------------- foreignColumn
	/**
	 * @return string
	 */
	function foreignColumn() : string
	{
		if (!$this->foreign_column) {
			$this->foreign_column = 'id_' . Names::setToSingle(
				Foreignlink_Annotation::of($this->property)->value
			);
		}
		return $this->foreign_column;
	}

	//---------------------------------------------------------------------------------- masterColumn
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string
	 */
	function masterColumn() : string
	{
		if (!$this->master_column) {
			$foreign_property_name = Foreign_Annotation::of($this->property)->value;
			$class = $this->property->getType()->asReflectionClass();
			/** @noinspection PhpUnhandledExceptionInspection property_exists */
			$foreign_property = property_exists($class->name, $foreign_property_name)
				? $class->getProperty($foreign_property_name)
				: null;
			$foreign_link = $foreign_property
				? Foreignlink_Annotation::of($foreign_property)->value
				: null;
			$master_column       = Names::setToSingle($foreign_link ?: $foreign_property_name);
			$this->master_column = 'id_' . $master_column;
		}
		return $this->master_column;
	}

	//----------------------------------------------------------------------------------------- table
	/**
	 * @return string
	 */
	function table() : string
	{
		if (!$this->table) {
			$table = $this->property->getAnnotation('set_store_name')->value;
			if ($table && is_string($table)) {
				$table       = $this->applyTableNameDefinitions($table);
				$this->table = strtolower($table);
			}
			else {
				$master_table  = Dao::storeNameOf($this->property->final_class);
				$foreign_table = Dao::storeNameOf($this->property->getType()->getElementTypeAsString());
				$this->table = $this->defaultStoreName($master_table, $foreign_table);
			}
		}
		return $this->table;
	}

}
