<?php
namespace SAF\Framework\Sql;

use SAF\Framework\Dao;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property;

/**
 * The SQL queries builder
 */
abstract class Builder
{

	//------------------------------------------------------------------------------- buildColumnName
	/**
	 * Builds column name from a property
	 *
	 * If property data type is an object, the property name will be prefixed with 'id_'.
	 * Array properties will return null as no column should be associated to them.
	 *
	 * @param $property Reflection_Property
	 * @return string|null
	 */
	public static function buildColumnName(Reflection_Property $property)
	{
		$type = $property->getType();
		return $type->isBasic()
			? $property->getAnnotation('storage')->value
			: ($type->isMultiple() ? null : ('id_' . $property->getAnnotation('storage')->value));
	}

	//----------------------------------------------------------------------------------- buildDelete
	/**
	 * Build a SQL DELETE query
	 *
	 * @param $class Reflection_Class|string
	 * @param $id    integer|integer[]
	 * @return string
	 */
	public static function buildDelete($class, $id)
	{
		if ($class instanceof Reflection_Class) {
			$class = $class->name;
		}
		$sql_delete = 'DELETE FROM ' . BQ . Dao::current()->storeNameOf($class) . BQ . LF . 'WHERE';
		if (is_numeric($id)) {
			$sql_delete .= ' id = ' . $id;
		}
		elseif (is_array($id)) {
			$first = true;
			foreach ($id as $key => $value) {
				$sql_delete .= $first ? ($first = false) : ' AND';
				$sql_delete .= ' ' . $key . ' = ' . Value::escape($value);
			}
		}
		else {
			trigger_error("id must be an integer of an array of integer values", E_USER_ERROR);
		}
		return $sql_delete;
	}

	//---------------------------------------------------------------------------------- buildColumns
	/**
	 * Build a SQL columns list
	 *
	 * @example used for INSERT INTO (columns), SELECT columns
	 * @param $column_names string[]
	 * @return string
	 */
	public static function buildColumns($column_names)
	{
		$sql_columns = '';
		$i = 0;
		foreach ($column_names as $column_name) {
			if ($i++) {
				$sql_columns .= ', ';
			}
			$sql_columns .= BQ . str_replace(DOT, BQ . DOT . BQ, $column_name) . BQ;
		}
		return $sql_columns;
	}

	//----------------------------------------------------------------------------------- buildInsert
	/**
	 * Build a SQL INSERT query
	 *
	 * @param $class Reflection_Class|string
	 * @param $write string[] the data to write for each column : key is the column name
	 * @return string
	 */
	public static function buildInsert($class, $write)
	{
		$sql_insert = 'INSERT INTO ' . BQ . Dao::current()->storeNameOf($class) . BQ . LF . 'SET' . SP;
		$i = 0;
		foreach ($write as $key => $value) {
			if ($i++) {
				$sql_insert .= ', ';
			}
			if (($key != 'id') && (substr($key, 0, 3) != 'id_')) {
				$key = BQ . $key . BQ;
			}
			$sql_insert .= $key . ' = ' . Value::escape($value);
		}
		return $sql_insert;
	}

	//----------------------------------------------------------------------------------- buildUpdate
	/**
	 * Build a SQL UPDATE query
	 *
	 * @param $class Reflection_Class|string
	 * @param $write array the data to write for each column : key is the column name
	 * @param $id    integer|integer[]
	 * @return string
	 */
	public static function buildUpdate($class, $write, $id)
	{
		$sql_update = 'UPDATE ' . BQ . Dao::current()->storeNameOf($class) . BQ . LF . 'SET ';
		$i = 0;
		foreach ($write as $key => $value) {
			if ($i++) {
				$sql_update .= ', ';
			}
			if (($key != 'id') && (substr($key, 0, 3) != 'id_')) {
				$key   = BQ . $key . BQ;
			}
			$sql_update .= $key . ' = ' . Value::escape($value);
		}
		$sql_update .= LF . 'WHERE';
		if (is_numeric($id)) {
			$sql_update .= ' id = ' . $id;
		}
		elseif (is_array($id)) {
			$first = true;
			foreach ($id as $key => $value) {
				$sql_update .= $first ? ($first = false) : ' AND';
				$sql_update .= ' ' . $key . ' = ' . $value;
			}
		}
		else {
			trigger_error("id must be an integer of an array of integer values", E_USER_ERROR);
		}
		return $sql_update;
	}

	//----------------------------------------------------------------------------------- buildValues
	/**
	 * @param $values string[] keys are columns names
	 * @return string
	 */
	public static function buildValues($values)
	{
		return join(', ', array_map([Value::class, 'escape'], $values));
	}

	//----------------------------------------------------------------------------- splitPropertyPath
	/**
	 * Split a property path into two parts : the master path and the foreign property name
	 *
	 * In fact it simply splits 'property.another.final' into 'property.another' and 'final'.
	 * If path is ony a single property name like 'this', master will be empty and foreign = property.
	 * You can change the foreign property name into a column name using buildColumnName().
	 *
	 * @example list($master_path, $foreign_property) = Sql_Builder::splitPropertyPath('a.full.path');
	 * $master_path will be 'a.full' and $foreign_property 'path'
	 * @param $path string
	 * @return array First element is the master property path, second element is the foreign property name
	 */
	public static function splitPropertyPath($path)
	{
		$i = strrpos($path, DOT);
		return ($i === false) ? ['', $path] : [substr($path, 0, $i), substr($path, $i + 1)];
	}

}
