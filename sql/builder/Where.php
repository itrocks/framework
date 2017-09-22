<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Logical;
use ITRocks\Framework\Dao\Sql\Link;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Storage_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Join;
use ITRocks\Framework\Sql\Join\Joins;
use ITRocks\Framework\Sql\Value;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\String_Class;

/**
 * The SQL where section of SQL queries builder
 */
class Where
{

	//---------------------------------------------------------------------------------------- $joins
	/**
	 * @var Joins
	 */
	private $joins;

	//------------------------------------------------------------------------------------- $sql_link
	/**
	 * Sql data link used for identifiers
	 *
	 * @var Link
	 */
	private $sql_link;

	//---------------------------------------------------------------------------------- $where_array
	/**
	 * Where array expression, keys are columns names
	 *
	 * @var array|Func\Where
	 */
	private $where_array;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct the SQL WHERE section of a query
	 *
	 * Supported columns naming forms are :
	 * column_name : column_name must correspond to a property of class
	 * column.foreign_column : column must be a property of class, foreign_column must be a property of column's var class
	 *
	 * @param $class_name  string base object class name
	 * @param $where_array array|Func\Where where array expression, keys are columns names
	 * @param $sql_link    Link
	 * @param $joins       Joins
	 */
	public function __construct(
		$class_name, $where_array = null, Link $sql_link = null, Joins $joins = null
	) {
		$this->joins       = $joins ? $joins : new Joins($class_name);
		$this->sql_link    = $sql_link ? $sql_link : Dao::current();
		$this->where_array = $where_array;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->build();
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * Build SQL WHERE section, add add joins for search criterion
	 *
	 * @param $union_optimization boolean If true, accepts an array as a result
	 *        for a OR logical function
	 * @return string|string[] if array, this is several WHERE clauses for an
	 *         optimized-union-instead-of-or.
	 */
	public function build($union_optimization = false)
	{
		$where_array = $this->where_array;
		if (
			$union_optimization
			&& ($where_array instanceof Func\Logical)
			&& $where_array->isOr()
		) {
			$sql = [];
			foreach ($where_array->arguments as $property_path => $argument) {
				$sql[] = LF . 'WHERE ' . $this->buildPath($property_path, $argument, 'AND');
			}
			return $sql;
		}
		$sql = ($this->where_array === false)
			? 'FALSE'
			: (
				is_null($this->where_array) ? '' : $this->buildPath('id', $this->where_array, 'AND', true)
			);
		return $sql ? (LF . 'WHERE ' . $sql) : $sql;
	}

	//------------------------------------------------------------------------------------ buildArray
	/**
	 * Build SQL WHERE section for multiple where clauses
	 *
	 * @param $path   string Base property path for values (if keys are numeric or structure keywords)
	 * @param $array  array An array of where conditions
	 * @param $clause string For multiple where clauses, tell if they are linked with 'OR' or 'AND'
	 * @return string
	 */
	private function buildArray($path, array $array, $clause)
	{
		$sql        = '';
		$sql_close  = '';
		$sub_clause = $clause;
		$first      = true;
		foreach ($array as $key => $value) {
			if ($first) $first = false; else $sql .= SP . $clause . SP;
			$key_clause = strtoupper($key);
			if (is_numeric($key) && ($value instanceof Logical)) {
				// if logical, simply build path as if key clause was 'AND' (the simplest)
				$key_clause = 'AND';
			}
			switch ($key_clause) {
				case 'NOT': $sql .= 'NOT (' . $this->buildPath($path, $value, 'AND') . ')';  break;
				case 'AND': $sql .= $this->buildPath($path, $value, $key_clause);             break;
				case 'OR':  $sql .= '(' . $this->buildPath($path, $value, $key_clause) . ')'; break;
				default:
					if (is_numeric($key)) {
						if ((count($array) > 1) && !$sql) {
							$sql       = '(';
							$clause    = 'OR';
							$sql_close = ')';
						}
						$build = $this->buildPath($path, $value, $sub_clause);
					}
					else {
						$prefix        = '';
						$master_path   = (($i = strrpos($path, DOT)) !== false) ? substr($path, 0, $i) : '';
						$property_name = ($i !== false) ? substr($path, $i + 1) : $path;
						$properties    = $this->joins->getProperties($master_path);
						if (isset($properties[$property_name])) {
							$property = $properties[$property_name];
							$link     = Link_Annotation::of($property)->value;
							if ($link) {
								$prefix = ($master_path ? ($master_path . DOT) : '')
									. Storage_Annotation::of($property)->value . DOT;
							}
						}
						$build = $this->buildPath($prefix . $key, $value, $sub_clause);
					}
					if (!empty($build)) {
						$sql .= $build;
					}
					elseif (!empty($sql)) {
						$sql = substr($sql, 0, -strlen(SP . $sub_clause . SP));
					}
					else {
						$first = true;
					}
			}
		}
		return $sql . $sql_close;
	}

	//----------------------------------------------------------------------------------- buildColumn
	/**
	 * @param $path   string The property path
	 * @param $prefix string A prefix for the name of the column @values '', 'id_'
	 * @return string The column name, with table alias and back-quotes @example 't0.`id_thing`'
	 */
	public function buildColumn($path, $prefix = '')
	{
		$join      = $this->joins->add($path);
		$link_join = $this->joins->getIdLinkJoin($path);
		if (isset($link_join)) {
			$column = $link_join->foreign_alias . '.`id`';
		}
		elseif (isset($join)) {
			if ($join->type === Join::LINK) {
				$column = $join->foreign_alias . DOT . BQ . rLastParse($path, DOT, 1, true) . BQ;
			}
			else {
				$property = $this->joins->getStartingClass()->getProperty($path);
				$column   = ($property && Link_Annotation::of($property)->isCollection())
					? $join->master_column
					: $join->foreign_column;
				$column = $join->foreign_alias . DOT . BQ . $column . BQ;
			}
		}
		else {
			list($master_path, $foreign_column) = Builder::splitPropertyPath($path);
			if (!$master_path && ($foreign_column === 'id')) {
				$class = $this->joins->getStartingClassName();
				$i     = 0;
				while ($class = (new Link_Class($class))->getLinkedClassName()) {
					$i ++;
				}
				$tx = 't' . $i;
			}
			else {
				$tx = 't0';
			}
			$column = ((!$master_path) || ($master_path === 'id'))
				? ($tx . DOT . BQ . $prefix . $foreign_column . BQ)
				: ($this->joins->getAlias($master_path) . DOT . BQ . $prefix . $foreign_column . BQ);
		}
		return $column;
	}

	//----------------------------------------------------------------------------------- buildObject
	/**
	 * Build SQL WHERE section for an object
	 *
	 * @param $path        string Base property path pointing to the object
	 * @param $object      object The value is an object, which will be used for search
	 * @param $root_object boolean if true, this is the root object : @link classes do not apply
	 * @return string
	 */
	private function buildObject($path, $object, $root_object = false)
	{
		$class = new Link_Class(get_class($object));
		if (!$root_object || !Class_\Link_Annotation::of($class)->value) {
			$id = $this->sql_link->getObjectIdentifier(
				$object,
				Class_\Link_Annotation::of($class)->value ? $class->getCompositeProperty()->name : null
			);
		}
		if (!empty($id)) {
			// object is linked to stored data : search with object identifier
			return $this->buildValue($path, $id, ($path === 'id') ? '' : 'id_');
		}
		// object is a search object : each property is a search entry, and must join table
		$this->joins->add($path);
		$array = [];
		$class = new Reflection_Class(get_class($object));
		foreach (
			Replaces_Annotations::removeReplacedProperties($class->accessProperties())
			as $property_name => $property
		) {
			$id_property_name = 'id_' . $property_name;
			if (isset($object->$property_name)) {
				$array[$property_name] = $object->$property_name;
			}
			elseif (isset($object->$id_property_name)) {
				$array[$id_property_name] = $object->$id_property_name;
			}
		}
		$sql = $this->buildArray($path, $array, 'AND');
		if (!$sql) {
			$sql = 'FALSE';
		}
		return $sql;
	}

	//------------------------------------------------------------------------------------- buildPath
	/**
	 * Build SQL WHERE section for given path and value
	 *
	 * @param $path      string|integer Property path starting by a root class property (may be a numeric key, or a structure keyword)
	 * @param $value     mixed May be a value, or a structured array of multiple where clauses
	 * @param $clause    string For multiple where clauses, tell if they are linked with 'OR' or 'AND'
	 * @param $root_path boolean
	 * @return string
	 */
	private function buildPath($path, $value, $clause, $root_path = false)
	{
		if ($value instanceof Func\Where) {
			$this->joins->add($path);
			list($master_path, $foreign_column) = Builder::splitPropertyPath($path);
			if ($foreign_column === 'id') {
				$prefix = '';
			}
			else {
				$properties = $this->joins->getProperties($master_path);
				$property   = isset($properties[$foreign_column]) ? $properties[$foreign_column] : null;
				$id_links   = [Link_Annotation::COLLECTION, Link_Annotation::MAP, Link_Annotation::OBJECT];
				$prefix     = '';

				if ($property && (Link_Annotation::of($property)->is($id_links))
					&& !(Store_Annotation::of($property)->is(Store_Annotation::STRING))
				) {
					$prefix = 'id_';
				}
			}
			return $value->toSql($this, $path, $prefix);
		}
		elseif ($value instanceof Date_Time) {
			// TODO a class annotation (@business? @string?) could help choose
			$value = $value->toISO(false);
		}
		switch (gettype($value)) {
			case 'NULL':   return $this->buildColumn($path) . ' IS NULL';
			case 'array':  return $this->buildArray($path, $value, $clause);
			case 'object': return ($value instanceof String_Class)
				? $this->buildValue($path, $value)
				: $this->buildObject($path, $value, $root_path);
			default:       return $this->buildValue($path, $value);
		}
	}

	//------------------------------------------------------------------------------------ buildValue
	/**
	 * Build SQL WHERE section for a unique value
	 *
	 * @param $path   string search property path
	 * @param $value  mixed search property value
	 * @param $prefix string Prefix for column name @values '', 'id_'
	 * @return string
	 */
	private function buildValue($path, $value, $prefix = '')
	{
		$column  = $this->buildColumn($path, $prefix);
		$is_like = Value::isLike($value);
		return $column . SP . ($is_like ? 'LIKE' : '=') . SP . Value::escape($value, $is_like);
	}

	//-------------------------------------------------------------------------------------- getJoins
	/**
	 * @return Joins
	 */
	public function getJoins()
	{
		return $this->joins;
	}

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * Gets the property of a path
	 *
	 * @param $path string
	 * @return Reflection_Property|null
	 */
	public function getProperty($path)
	{
		list($master_path, $foreign_column) = Builder::splitPropertyPath($path);
		$properties = $this->joins->getProperties($master_path);
		$property = isset($properties[$foreign_column]) ? $properties[$foreign_column] : null;
		return $property;
	}

	//------------------------------------------------------------------------------------ getSqlLink
	/**
	 * Gets used Sql_Link as defined on constructor call
	 *
	 * @return Link
	 */
	public function getSqlLink()
	{
		return $this->sql_link;
	}

	//--------------------------------------------------------------------------------- getWhereArray
	/**
	 * @return array|Func\Where|null
	 */
	public function getWhereArray()
	{
		return $this->where_array;
	}

}
