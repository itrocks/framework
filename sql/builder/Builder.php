<?php
namespace ITRocks\Framework\Sql;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Values_Annotation;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ReflectionClass;

/**
 * The SQL queries builder
 */
abstract class Builder
{

	//---------------------------------------------------------------------------------------- DELETE
	const DELETE = 'DELETE';

	//---------------------------------------------------------------------------------------- INSERT
	const INSERT = 'INSERT';

	//--------------------------------------------------------------------------------------- REPLACE
	const REPLACE = 'REPLACE';

	//---------------------------------------------------------------------------------------- SELECT
	const SELECT = 'SELECT';

	//---------------------------------------------------------------------------------------- UPDATE
	const UPDATE = 'UPDATE';

	//------------------------------------------------------------------------------- buildColumnName
	/**
	 * Builds column name from a property
	 *
	 * If property data type is an object, the property name will be prefixed with 'id_'.
	 * Array properties will return null as no column should be associated to them.
	 *
	 * @param $property Reflection_Property
	 * @return ?string
	 */
	public static function buildColumnName(Reflection_Property $property) : ?string
	{
		$type = $property->getType();
		return $type->isBasic()
			? Store_Name_Annotation::of($property)->value
			: (
				(
					$type->isMultiple()
					|| (
						$property->getAnnotation('component')->value
						&& Link_Annotation::of($property)->isObject()
					)
				)
				? null
				: ('id_' . Store_Name_Annotation::of($property)->value)
			);
	}

	//---------------------------------------------------------------------------------- buildColumns
	/**
	 * Build a SQL columns list
	 *
	 * @example used for INSERT INTO (columns), SELECT columns
	 * @param $column_names string[]
	 * @return string
	 */
	public static function buildColumns(array $column_names) : string
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

	//----------------------------------------------------------------------------------- buildDelete
	/**
	 * Build a SQL DELETE query
	 *
	 * @param $class Reflection_Class|string
	 * @param $id    integer|integer[]
	 * @return string
	 */
	public static function buildDelete(Reflection_Class|string $class, array|int $id) : string
	{
		if ($class instanceof Reflection_Class) {
			$class = $class->name;
		}
		$sql_delete = self::DELETE . ' FROM ' . BQ . Dao::current()->storeNameOf($class) . BQ . LF
			. 'WHERE';
		if (is_numeric($id)) {
			$sql_delete .= ' id = ' . $id;
		}
		elseif (is_array($id)) {
			$first = true;
			foreach ($id as $key => $value) {
				$sql_delete .= $first ? ($first = false) : ' AND';
				$sql_delete .= SP . BQ . $key . BQ . ' = ' . Value::escape($value);
			}
		}
		else {
			trigger_error("id must be an integer of an array of integer values", E_USER_ERROR);
		}
		return $sql_delete;
	}

	//----------------------------------------------------------------------------------- buildInsert
	/**
	 * Build a SQL INSERT query
	 *
	 * @param $class            string|Reflection_Class
	 * @param $write            string[] the data to write for each column : key is the column name
	 * @param $write_properties Reflection_Property[] key is the column name
	 * @return string
	 */
	public static function buildInsert(
		string|Reflection_Class $class, array $write, array $write_properties = []
	) : string
	{
		return self::INSERT . ' INTO ' . static::buildSet($class, $write, $write_properties);
	}

	//-------------------------------------------------------------------------------------- buildSet
	/**
	 * Build the SET part of a SQL INSERT or UPDATE query
	 *
	 * @param $class            Reflection_Class|string
	 * @param $write            array the data to write for each column : key is the column name
	 * @param $write_properties Reflection_Property[] key is the column name
	 * @return string
	 */
	protected static function buildSet(
		Reflection_Class|string $class, array $write, array $write_properties
	) : string
	{
		$sql     = BQ . Dao::current()->storeNameOf($class) . BQ . LF . 'SET' . SP;
		$already = 0;
		foreach ($write as $key => $value) {
			$property = $write_properties[$key] ?? null;
			if ($already ++) {
				$sql .= ', ';
			}
			if (
				$property
				&& $property->getType()->isMultipleString()
				&& !Values_Annotation::of($property)->value
				&& !Store::of($property)->isString()
			) {
				$value = join(LF, $value);
			}
			$sql .= BQ . $key . BQ . ' = ' . Value::escape($value, false, $property);
		}
		return $sql;
	}

	//----------------------------------------------------------------------------------- buildUpdate
	/**
	 * Build a SQL UPDATE query
	 *
	 * @param $class            Reflection_Class|string
	 * @param $write            array the data to write for each column : key is the column name
	 * @param $id               integer|integer[]
	 * @param $write_properties Reflection_Property[] key is the column name
	 * @return string
	 */
	public static function buildUpdate(
		string|Reflection_Class $class, array $write, array|int $id, array $write_properties = []
	) : string
	{
		$sql_update  = static::UPDATE . SP . static::buildSet($class, $write, $write_properties);
		$sql_update .= LF . 'WHERE';
		if (is_numeric($id)) {
			$sql_update .= ' id = ' . $id;
		}
		elseif (is_array($id)) {
			$first = true;
			foreach ($id as $key => $value) {
				$sql_update .= $first ? ($first = false) : ' AND';
				$sql_update .= SP . BQ . $key . BQ . ' = ' . Value::escape($value);
			}
		}
		else {
			trigger_error('id must be an integer of an array of integer values', E_USER_ERROR);
		}
		return $sql_update;
	}

	//----------------------------------------------------------------------------------- buildValues
	/**
	 * @param $values string[] keys are columns names
	 * @return string
	 */
	public static function buildValues(array $values) : string
	{
		return join(', ', array_map([Value::class, 'escape'], $values));
	}

	//--------------------------------------------------------------------------------- getObjectVars
	/**
	 * Same as get_object_vars, but for objects that may have AOP / identifiers : keep only read
	 * properties values
	 *
	 * @param $object object
	 * @return array
	 */
	public static function getObjectVars(object $object) : array
	{
		$vars = [];
		foreach ((new ReflectionClass($object))->getProperties() as $property) {
			$value = $property->getValue($object);
			if (is_array($value)) {
				$value = DQ . join(',', $value) . DQ;
			}
			$vars[$property->name] = $value;
		}
		return $vars;
	}

	//----------------------------------------------------------------------------- splitPropertyPath
	/**
	 * Split a property path into two parts : the master path and the foreign property name
	 *
	 * In fact it simply splits 'property.another.final' into 'property.another' and 'final'.
	 * If path is ony a single property name like 'this', master will be empty and foreign = property.
	 * You can change the foreign property name into a column name using buildColumnName().
	 *
	 * @example [$master_path, $foreign_property] = Sql_Builder::splitPropertyPath('a.full.path');
	 * $master_path will be 'a.full' and $foreign_property 'path'
	 * @param $path string
	 * @return array First element is the master property path, second element is the foreign property name
	 */
	public static function splitPropertyPath(string $path) : array
	{
		// deal with "ITRocks\Framework\Locale\Translation(text=document.name,language='fr')"
		if (($par = strpos($path, '(')) && ($dot = strpos($path, DOT))) {
			while ($par < $dot) {
				$close = strpos($path, ')', $par);
				$par   = strpos($path, '(', $close) ?: strlen($path);
				$dot   = strpos($path, DOT, $close);
			}
			$i = $dot;
		}
		// the easy way
		else {
			$i = strrpos($path, DOT);
		}
		return ($i === false) ? ['', $path] : [substr($path, 0, $i), substr($path, $i + 1)];
	}

}
