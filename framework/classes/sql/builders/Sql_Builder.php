<?php
namespace SAF\Framework;

abstract class Sql_Builder
{

	//------------------------------------------------------------------------------- buildColumnName
	/**
	 * Builds column name from a property
	 *
	 * If property data type is an object, the property name will be prefixed with "id_".
	 * Array properties will return null as no column should be associated to them.
	 *
	 * @param Reflection_Property $property
	 * @return string | null
	 */
	public static function buildColumnName(Reflection_Property $property)
	{
		$type = $property->getType();
		return Type::isBasic($type)
			? $property->name
			: (Type::isMultiple($type) ? null : ("id_" . $property->name));
	}

	//----------------------------------------------------------------------------------- buildDelete
	/**
	 * Build a SQL DELETE query
	 * 
	 * @param Reflection_Class | string $class
	 * @param integer $id
	 * @return string
	 */
	public static function buildDelete($class, $id)
	{
		return "DELETE FROM `" . Dao::current()->storeNameOf($class) . "`"
			. " WHERE id = " . $id;
	}

	//---------------------------------------------------------------------------------- buildColumns
	/**
	 * Build a SQL columns list
	 *
	 * @example used for INSERT INTO (columns), SELECT columns
	 * @param multitype:string $column_names
	 * @return string
	 */
	public static function buildColumns($column_names)
	{
		$sql_columns = "";
		$i = count($column_names);
		foreach ($column_names as $column_name) {
			$sql_columns .= "`" . str_replace(".", "`.`", $column_name) . "`";
			if (--$i > 0) {
				$sql_columns .= ", ";
			}
		}
		return $sql_columns;
	}

	//----------------------------------------------------------------------------------- buildInsert
	/**
	 * Build a SQL INSERT query
	 * 
	 * @param Reflection_Class | string $class
	 * @param multitype:mixed $write the data to write for each column : key is the column name
	 * @return string
	 */
	public static function buildInsert($class, $write)
	{
		$build_columns = static::buildColumns(array_keys($write));
		if (!$build_columns) {
			return null;
		}
		else {
			$insert = "INSERT INTO `" . Dao::current()->storeNameOf($class) . "`"
				. " (" . $build_columns . ") VALUES ("
				. static::buildValues($write) . ")";
			return $insert;
		}
	}

	//----------------------------------------------------------------------------------- buildSelect
	/**
	 * Construct a SQL SELECT query
	 *
	 * Supported columns naming forms are :
	 * column_name : column_name must correspond to a property of class
	 * column.foreign_column : column must be a property of class, foreign_column must be a property of column's @var class
	 *
	 * @param string           $class base object class name
	 * @param multitype:string $properties properties paths list
	 * @param array            $where_array where array expression, indices are columns names
	 * @param Sql_Link         $sql_link
	 */
	public static function buildSelect(
		$class, $properties, $where_columns = null, Sql_Link $sql_link = null
	) {
		$sql_select_builder = new Sql_Select_Builder($class, $properties, $where_columns, $sql_link);
		return $sql_select_builder->buildQuery();
	}

	//----------------------------------------------------------------------------------- buildUpdate
	/**
	 * Build a SQL UPDATE query
	 *
	 * @param Reflection_Class | string $class
	 * @param array $write the data to write for each column : key is the column name
	 * @param integer $id
	 * @return string
	 */
	public static function buildUpdate($class, $write, $id)
	{
		$sql_update = "UPDATE `" . Dao::current()->storeNameOf($class) . "` SET ";
		$do = false;
		foreach ($write as $key => $value) {
			$value = Sql_Value::escape($value);
			if ($do) $sql_update .= ", "; else $do = true;
			$sql_update .= "`" . $key . "` = " . $value;
		}
		$sql_update .= " WHERE id = " . $id;
		return $sql_update;
	}

	//----------------------------------------------------------------------------------- buildValues
	public static function buildValues($values)
	{
		return join(", ", array_map(array("SAF\\Framework\\Sql_Value", "escape"), $values));
	}

	//----------------------------------------------------------------------------- splitPropertyPath
	/**
	 * Split a property path into two parts : the master path and the foreign property name
	 *
	 * In fact it simply splits "property.another.final" into "property.another" and "final".
	 * If path is ony a single property name like "this", master will be empty and foreign = property.
	 * You can change the foreign property name into a column name using buildColumnName().
	 *
	 * @example list($master_path, $foreign_property) = Sql_Builder::splitPropertyPath("a.full.path");
	 * $master_path will be "a.full" and $foreign_property "path"
	 * @param string $path
	 * @return array First element is the master property path, second element is the foreign property name
	 */
	public static function splitPropertyPath($path)
	{
		$i = strrpos($path, ".");
		return ($i === false)
			? array("", $path)
			: array(substr($path, 0, $i), substr($path, $i + 1));
	}

}
