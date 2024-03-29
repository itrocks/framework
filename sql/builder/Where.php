<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Expression;
use ITRocks\Framework\Dao\Func\Expressions;
use ITRocks\Framework\Dao\Func\Logical;
use ITRocks\Framework\Dao\Sql\Link;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Join;
use ITRocks\Framework\Sql\Join\Joins;
use ITRocks\Framework\Sql\Value;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\String_Class;
use ReflectionException;

/**
 * The SQL where section of SQL queries builder
 */
class Where implements With_Build_Column
{
	use Has_Build_Column;

	//-------------------------------------------------------------------------------- $built_columns
	/**
	 * A list of built columns
	 *
	 * @var string[]
	 */
	public array $built_columns = [];

	//-------------------------------------------------------------------------------------- $keyword
	/**
	 * @var string
	 */
	public string $keyword = 'WHERE';

	//------------------------------------------------------------------------------------- $sql_link
	/**
	 * Sql data link used for identifiers
	 *
	 * @var Link
	 */
	private Link $sql_link;

	//---------------------------------------------------------------------------------- $where_array
	/**
	 * Where array expression, keys are columns names
	 *
	 * @var array|object|null
	 */
	private array|object|null $where_array;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct the SQL WHERE section of a query
	 *
	 * Supported columns naming forms are :
	 * column_name : column_name must correspond to a property of class
	 * column.foreign_column : column must be a property of class, foreign_column must be a property
	 *   of the column's var class
	 *
	 * @param $where_array array|object|null where array expression, keys are columns names
	 * @param $sql_link    Link|null
	 * @param $joins       Joins
	 */
	public function __construct(array|object|null $where_array, Link|null $sql_link, Joins $joins)
	{
		$this->joins       = $joins;
		$this->sql_link    = $sql_link ?: Dao::current();
		$this->where_array = $where_array;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
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
	public function build(bool $union_optimization = false) : array|string
	{
		$where_array = $this->where_array;
		if (
			$union_optimization
			&& ($where_array instanceof Func\Logical)
			&& $where_array->isOr()
		) {
			$sql = [];
			foreach ($where_array->arguments as $property_path => $argument) {
				$sql[] = LF . $this->keyword . SP . $this->buildPath($property_path, $argument, 'AND');
			}
			return $sql;
		}
		$sql = ($this->where_array === false)
			? 'FALSE'
			: (
				is_null($this->where_array) ? '' : $this->buildPath('id', $this->where_array, 'AND', true)
			);
		return $sql ? (LF . $this->keyword . SP . $sql) : $sql;
	}

	//------------------------------------------------------------------------------------ buildArray
	/**
	 * Build SQL WHERE section for multiple where clauses
	 *
	 * @param $path   Expression|string Base property path for values
	 *                (if keys are numeric or structure keywords)
	 * @param $array  array An array of where conditions
	 * @param $clause string For multiple where clauses, tell if they are linked with 'OR' or 'AND'
	 * @return string
	 */
	private function buildArray(Expression|string $path, array $array, string $clause) : string
	{
		$property_path = strval($path);
		$sql           = '';
		$sql_close     = '';
		$sub_clause    = $clause;
		$first         = true;
		foreach ($array as $key => $value) {
			if ($first) {
				$first = false;
			}
			else {
				$sql .= SP . $clause . SP;
			}
			if ($key && is_string($key) && Expressions::isFunction($key)) {
				$key = Expressions::$current->cache[$key];
			}
			$key_clause = is_string($key) ? strtoupper($key) : null;
			if (is_numeric($key) && ($value instanceof Logical)) {
				// if logical, simply build path as if key clause was 'AND' (the simplest)
				$key_clause = 'AND';
			}
			switch ($key_clause) {
				case 'NOT': $sql .= 'NOT (' . $this->buildPath($path, $value, 'AND') . ')';  break;
				case 'AND': $sql .= $this->buildPath($path, $value, $key_clause);             break;
				case 'OR':
					$sql .= (count($value) > 1)
						? ('(' . $this->buildPath($path, $value, $key_clause) . ')')
						: $this->buildPath($path, $value, $key_clause);
					break;
				default:
					if (is_numeric($key)) {
						if ((count($array) > 1) && !$sql && arrayKeysAllNumeric($array, true)) {
							$sql       = '(';
							$clause    = 'OR';
							$sql_close = ')';
						}
						$build = $this->buildPath($path, $value, $sub_clause);
					}
					else {
						/** @noinspection DuplicatedCode too much complicated to mutualise for 4 lines */
						$prefix      = '';
						$master_path = (($i = strrpos($property_path, DOT)) !== false)
							? substr($property_path, 0, $i) : '';
						$property_name = ($i !== false) ? substr($property_path, $i + 1) : $property_path;
						$properties    = $this->joins->getProperties($master_path);
						if (isset($properties[$property_name])) {
							$property = $properties[$property_name];
							$link     = Link_Annotation::of($property)->value;
							if ($link) {
								$prefix = ($master_path ? ($master_path . DOT) : '')
									. Store_Name_Annotation::of($property)->value . DOT;
								if ($key instanceof Expression) {
									$key->prefix = $prefix;
								}
								else {
									$key = $prefix . $key;
								}
							}
						}
						$build = $this->buildPath($key, $value, $sub_clause);
						if (
							is_object($value)
							&& isset($properties[$key])
							&& ($property = $properties[$key])
							&& $property->getType()->isClass()
							&& $property->getType()->isAbstractClass()
						) {
							$class_name = Framework\Builder::current()->sourceClassName(get_class($value));
							$build .= ' AND ' . $this->buildValue($key . '_class', $class_name,  'id_');
						}
						if ($prefix && ($key instanceof Expression)) {
							$key->prefix = null;
						}
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

	//----------------------------------------------------------------------------------- buildObject
	/**
	 * Build SQL WHERE section for an object
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $path        Expression|string Base property path pointing to the object
	 * @param $object      object The value is an object, which will be used for search
	 * @param $root_object boolean if true, this is the root object : @link classes do not apply
	 * @return string
	 */
	private function buildObject(
		Expression|string $path, object $object, bool $root_object = false
	) : string
	{
		if ($path instanceof Expression) {
			trigger_error(
				"Can't associate object property path $path to "
					. get_class($path->function) . ' function call',
				E_USER_ERROR
			);
		}
		/** @noinspection PhpUnhandledExceptionInspection object */
		$class = new Link_Class($object);
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
		/** @noinspection PhpUnhandledExceptionInspection object */
		$class = new Reflection_Class($object);
		foreach (
			Replaces_Annotations::removeReplacedProperties($class->getProperties())
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
	 * @param $path      integer|string|Expression Property path starting by a root class property
	 *                   (may be a numeric key, or a structure keyword, or an Expression)
	 * @param $value     mixed May be a value, or a structured array of multiple where clauses
	 * @param $clause    string For multiple where clauses, tell if they are linked with 'OR' or 'AND'
	 * @param $root_path boolean
	 * @return string
	 */
	private function buildPath(
		int|Expression|string $path, mixed $value, string $clause, bool $root_path = false
	) : string
	{
		$property_path = strval($path);

		if ($value instanceof Func\Where) {
			$this->joins->add($property_path);
			[$master_path, $foreign_column] = Builder::splitPropertyPath($property_path);
			if ($foreign_column === 'id') {
				$prefix = '';
			}
			else {
				$properties = $this->joins->getProperties($master_path);
				$property   = $properties[$foreign_column] ?? null;
				$id_links   = [Link_Annotation::COLLECTION, Link_Annotation::MAP, Link_Annotation::OBJECT];
				$prefix     = '';

				if (
					$property
					&& (Link_Annotation::of($property)->is($id_links))
					&& !Store::of($property)->isString()
				) {
					$prefix = 'id_';
				}
			}
			// TODO Manage the case of $path instanceof Expression
			return $value->toSql($this, $path, $prefix);
		}
		elseif ($value instanceof Date_Time) {
			// TODO a class annotation (#Store? @string?) could help choose
			$value = $value->toISO(false);
		}
		switch (gettype($value)) {
			case 'NULL':
				return $this->buildWhereColumn($path) . ' IS NULL';
			case 'array':
				return $this->buildArray($path, $value, $clause);
			case 'object':
				return ($value instanceof String_Class)
					? $this->buildValue($path, $value)
					: $this->buildObject($path, $value, $root_path);
			default:
				return $this->buildValue($path, $value);
		}
	}

	//------------------------------------------------------------------------------------ buildValue
	/**
	 * Build SQL WHERE section for a unique value
	 *
	 * @param $path   Expression|string search property path or Expression
	 * @param $value  mixed search property value
	 * @param $prefix string Prefix for column name @values '', 'id_'
	 * @return string
	 */
	private function buildValue(Expression|string $path, mixed $value, string $prefix = '') : string
	{
		$column  = $this->buildWhereColumn($path, $prefix);
		$is_like = Value::isLike($value);
		return $column . SP . ($is_like ? 'LIKE' : '=') . SP
			. Value::escape($value, $is_like, $this->getProperty(strval($path)));
	}

	//------------------------------------------------------------------------------ buildWhereColumn
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $path   Expression|string The property path or Expression
	 * @param $prefix string A prefix for the name of the column @values '', 'id_'
	 * @return string The column name, with table alias and back-quotes @example 't0.id_thing'
	 */
	public function buildWhereColumn(Expression|string $path, string $prefix = '') : string
	{
		if (Expressions::isFunction($path)) {
			$path = Expressions::$current->cache[$path];
		}
		$property_path = strval($path);
		try {
			$property = (!str_contains($property_path, '->') && !str_contains($property_path, ')'))
				? $this->joins->getStartingClass()->getProperty($property_path)
				: null;
		}
		catch (ReflectionException) {
			$property = null;
		}

		$join = ($property && ($property->getType()->asString() === 'object'))
			? null
			: $this->joins->add($property_path);
		$link_join = $this->joins->getIdLinkJoin($property_path);

		if ($link_join) {
			$column = $link_join->foreign_alias . '.id';
		}
		elseif ($join) {
			if ($join->type === Join::LINK) {
				$column = $join->foreign_alias . DOT . BQ . rLastParse($property_path, DOT, 1, true) . BQ;
			}
			else {
				$column = ($property && Link_Annotation::of($property)->isCollection())
					? $join->master_column
					: $join->foreign_column;
				$column = $join->foreign_alias . DOT . BQ . $column . BQ;
			}
		}
		else {
			[$master_path, $foreign_column] = Builder::splitPropertyPath($property_path);
			if (!$master_path && ($foreign_column === 'id')) {
				$class = $this->joins->getStartingClassName();
				$i     = 0;
				/** @noinspection PhpUnhandledExceptionInspection starting class name always valid */
				while ($class = (new Link_Class($class))->getLinkedClassName()) {
					$i ++;
				}
				$tx = $this->joins->alias_prefix . 't' . $i;
			}
			else {
				$tx = $this->joins->rootAlias();
			}
			$column = ((!$master_path) || ($master_path === 'id'))
				? ($tx . DOT . BQ . $prefix . $foreign_column . BQ)
				: ($this->joins->getAlias($master_path) . DOT . BQ . $prefix . $foreign_column . BQ);
		}
		if ($path instanceof Expression) {
			$column = $path->function->toSql($this, $column);
		}
		$this->built_columns[$column] = $column;
		return $column;
	}

	//-------------------------------------------------------------------------------------- getJoins
	/**
	 * @return Joins
	 */
	public function getJoins() : Joins
	{
		return $this->joins;
	}

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * Gets the property of a path
	 *
	 * @param $path string
	 * @return ?Reflection_Property
	 */
	public function getProperty(string $path) : ?Reflection_Property
	{
		[$master_path, $foreign_column] = Builder::splitPropertyPath($path);
		$properties = $this->joins->getProperties($master_path);
		return $properties[$foreign_column] ?? null;
	}

	//------------------------------------------------------------------------------------ getSqlLink
	/**
	 * Gets used Sql_Link as defined on constructor call
	 *
	 * @return Link
	 */
	public function getSqlLink() : Link
	{
		return $this->sql_link;
	}

	//--------------------------------------------------------------------------------- getWhereArray
	/**
	 * @return array|object|null
	 */
	public function getWhereArray() : array|object|null
	{
		return $this->where_array;
	}

	//-------------------------------------------------------------------------------------- restrict
	/**
	 * @param $where_array array|object|null
	 */
	public function restrict(array|object|null $where_array) : void
	{
		$this->where_array = $this->where_array
			? ['AND' => array_merge($where_array, [$this->where_array])]
			: $where_array;
	}

}
