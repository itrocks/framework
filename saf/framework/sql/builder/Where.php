<?php
namespace SAF\Framework\Sql\Builder;

use SAF\Framework\Dao\Func;
use SAF\Framework\Dao\Func\Logical;
use SAF\Framework\Dao\Sql\Link;
use SAF\Framework\Dao;
use SAF\Framework\Reflection\Annotation\Property\Link_Annotation;
use SAF\Framework\Reflection\Link_Class;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Sql\Builder;
use SAF\Framework\Sql\Join\Joins;
use SAF\Framework\Sql\Join;
use SAF\Framework\Sql\Value;
use SAF\Framework\Tools\Date_Time;

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
	 * Where array expression, indices are columns names
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
	 * @param $where_array array|Func\Where where array expression, indices are columns names
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
		$sql = is_null($this->where_array) ? '' : $this->buildPath('id', $this->where_array, 'AND');
		return $sql ? (LF . 'WHERE ' . $sql) : $sql;
	}

	//------------------------------------------------------------------------------------ buildArray
	/**
	 * Build SQL WHERE section for multiple where clauses
	 *
	 * @param $path        string Base property path for values (if keys are numeric or structure keywords)
	 * @param $array       array An array of where conditions
	 * @param $clause      string For multiple where clauses, tell if they are linked with 'OR' or 'AND'
	 * @return string
	 */
	private function buildArray($path, $array, $clause)
	{
		$sql = '';
		$sql_close = '';
		$sub_clause = $clause;
		$first = true;
		foreach ($array as $key => $value) {
			if ($first) $first = false; else $sql .= SP . $clause . SP;
			$key_clause = strtoupper($key);
			if (is_numeric($key) && ($value instanceof Logical)) {
				// if logical, simply build path as if key clause was 'AND' (the simpliest)
				$key_clause = 'AND';
			}
			switch ($key_clause) {
				case 'NOT': $sql .= 'NOT (' . $this->buildPath($path, $value, 'AND') . ')';  break;
				case 'AND': $sql .= $this->buildPath($path, $value, $key_clause);             break;
				case 'OR':  $sql .= '(' . $this->buildPath($path, $value, $key_clause) . ')'; break;
				default:
					if (is_numeric($key)) {
						if ((count($array) > 1) && !$sql) {
							$sql = '(';
							$clause = 'OR';
							$sql_close = ')';
						}
						$build = $this->buildPath($path, $value, $sub_clause);
					}
					else {
						$prefix = '';
						$master_path = (($i = strrpos($path, DOT)) !== false) ? substr($path, 0, $i) : '';
						$property_name = ($i !== false) ? substr($path, $i + 1) : $path;
						$properties = $this->joins->getProperties($master_path);
						if (isset($properties[$property_name])) {
							$property = $properties[$property_name];
							$link = $property->getAnnotation('link')->value;
							if ($link) {
								$prefix = ($master_path ? ($master_path . DOT) : '')
									. $property->getAnnotation('storage')->value . DOT;
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
			}
		}
		return $sql . $sql_close;
	}

	//----------------------------------------------------------------------------------- buildColumn
	/**
	 * @param $path   string
	 * @param $prefix string
	 * @return string
	 */
	public function buildColumn($path, $prefix = '')
	{
		$join = $this->joins->add($path);
		$link_join = $this->joins->getIdLinkJoin($path);
		if (isset($link_join)) {
			$column = $link_join->foreign_alias . DOT . 'id';
		}
		elseif (isset($join)) {
			if ($join->type === Join::LINK) {
				$column = $join->foreign_alias . DOT . BQ . rLastParse($path, DOT, 1, true) . BQ;
			}
			else {
				$column = (
					$this->joins->getStartingClass()->getProperty($path)->getAnnotation('link')->value
					== Link_Annotation::COLLECTION
				)
					? $join->master_column
					: $join->foreign_column;
				$column = $join->foreign_alias . DOT . BQ . $column . BQ;
			}
		}
		else {
			list($master_path, $foreign_column) = Builder::splitPropertyPath($path);
			if (!$master_path && $foreign_column == 'id') {
				$class = $this->joins->getStartingClassName();
				$i = 0;
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
	 * @return string
	 */
	private function buildObject($path, $object)
	{
		$class = new Link_Class(get_class($object));
		$id = $this->sql_link->getObjectIdentifier(
			$object,
			$class->getAnnotation('link')->value ? $class->getCompositeProperty()->name : null
		);
		if ($id) {
			// object is linked to stored data : search with object identifier
			return $this->buildValue($path, $id, ($path == 'id') ? '' : 'id_');
		}
		// object is a search object : each property is a search entry, and must join table
		$this->joins->add($path);
		$array = [];
		$class = new Reflection_Class(get_class($object));
		foreach ($class->accessProperties() as $property_name => $property) {
			if (isset($object->$property_name)) {
				$sub_path = $property_name;
				$array[$sub_path] = $object->$property_name;
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
	 * @param $path        string|integer Property path starting by a root class property (may be a numeric key, or a structure keyword)
	 * @param $value       mixed May be a value, or a structured array of multiple where clauses
	 * @param $clause      string For multiple where clauses, tell if they are linked with 'OR' or 'AND'
	 * @return string
	 */
	private function buildPath($path, $value, $clause)
	{
		if ($value instanceof Func\Where) {
			$this->joins->add($path);
			list($master_path, $foreign_column) = Builder::splitPropertyPath($path);
			if ($foreign_column == 'id') {
				$prefix = '';
			}
			else {
				$properties = $this->joins->getProperties($master_path);
				$property = isset($properties[$foreign_column]) ? $properties[$foreign_column] : null;
				$id_links = [Link_Annotation::OBJECT, Link_Annotation::COLLECTION, Link_Annotation::MAP];
				$prefix = $property
					? (in_array($property->getAnnotation('link')->value, $id_links) ? 'id_' : '')
					: '';
			}
			return $value->toSql($this, $path, $prefix);
		}
		elseif ($value instanceof Date_Time) {
			// TODO a class annotation (@business? @string?) could help choose
			$value = $value->toISO(false);
		}
		switch (gettype($value)) {
			case 'NULL':   return '';
			case 'array':  return $this->buildArray ($path, $value, $clause);
			case 'object': return $this->buildObject($path, $value);
			default:       return $this->buildValue ($path, $value);
		}
	}

	//------------------------------------------------------------------------------------ buildValue
	/**
	 * Build SQL WHERE section for a unique value
	 *
	 * @param $path   string search property path
	 * @param $value  mixed search property value
	 * @param $prefix string Prefix for column name
	 * @return string
	 */
	private function buildValue($path, $value, $prefix = '')
	{
		$column = $this->buildColumn($path, $prefix);
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

}
