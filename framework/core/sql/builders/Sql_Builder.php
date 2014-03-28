<?php
namespace SAF\Framework;

/**
 * The SQL queries builder
 */
abstract class Sql_Builder
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
	 * @param $class     Reflection_Class | string
	 * @param $id        integer
	 * @return string
	 */
	public static function buildDelete($class, $id)
	{
		return 'DELETE FROM ' . BQ . Dao::current()->storeNameOf($class) . BQ
			. ' WHERE id = ' . $id;
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
		$i = count($column_names);
		foreach ($column_names as $column_name) {
			$sql_columns .= BQ . str_replace(DOT, BQ . DOT . BQ, $column_name) . BQ;
			if (--$i > 0) {
				$sql_columns .= ', ';
			}
		}
		return $sql_columns;
	}

	//----------------------------------------------------------------------------------- buildInsert
	/**
	 * Build a SQL INSERT query
	 *
	 * @param $class Reflection_Class | string
	 * @param $write string[] the data to write for each column : key is the column name
	 * @return string
	 */
	public static function buildInsert($class, $write)
	{
		$build_columns = static::buildColumns(array_keys($write));
		if (!$build_columns) {
			return null;
		}
		else {
			$insert = 'INSERT INTO ' . BQ . Dao::current()->storeNameOf($class) . BQ
				. ' (' . $build_columns . ') VALUES ('
				. static::buildValues($write) . ')';
			return $insert;
		}
	}

	//----------------------------------------------------------------------------------- buildUpdate
	/**
	 * Build a SQL UPDATE query
	 *
	 * @param $class Reflection_Class | string
	 * @param $write array the data to write for each column : key is the column name
	 * @param $id integer
	 * @return string
	 */
	public static function buildUpdate($class, $write, $id)
	{
		$sql_update = 'UPDATE ' . BQ . Dao::current()->storeNameOf($class) . BQ . ' SET ';
		$do = false;
		foreach ($write as $key => $value) {
			$value = Sql_Value::escape($value);
			if ($do) $sql_update .= ', '; else $do = true;
			$sql_update .= BQ . $key . BQ . ' = ' . $value;
		}
		$sql_update .= ' WHERE id = ' . $id;
		return $sql_update;
	}

	//----------------------------------------------------------------------------------- buildValues
	/**
	 * @param $values string[] keys are columns names
	 * @return string
	 */
	public static function buildValues($values)
	{
		return join(', ', array_map([Sql_Value::class, 'escape'], $values));
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
