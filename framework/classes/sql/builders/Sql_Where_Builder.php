<?php
namespace SAF\Framework;

trait Sql_Where_Builder
{
	use Sql_Joins_Builder;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * Root class
	 *
	 * @var string
	 */
	private $class;

	//------------------------------------------------------------------------------------- $sql_link
	/**
	 * Sql data link used for identifiers
	 *
	 * @var Sql_Link
	 */
	private $sql_link;

	//---------------------------------------------------------------------------------- $where_array
	/**
	 * Where array expression, indices are columns names
	 *
	 * @var array
	 */
	private $where_array;

	//----------------------------------------------------------------------------------------- build
	/**
	 * Build SQL WHERE section for given path and value
	 *
	 * @param string | integer $path   Property path starting by a root class property (may be a numeric key, or a structure keyword)
	 * @param mixed            $value  May be a value, or a structured array of multiple where clauses
	 * @param string           $clause For multiple where clauses, tell if they are linked with "OR" or "AND"
	 */
	private function build($path, $value, $clause)
	{
		switch (gettype($value)) {
			case "NULL":   return "";
			case "array":  return $this->buildArray($path, $value, $clause);
			case "object": return $this->buildObject($path, $value);
			default:       return $this->buildValue($path, $value);
		}
	}

	//------------------------------------------------------------------------------------ buildArray
	/**
	 * Build SQL WHERE section for multiple where clauses
	 *
	 * @param string $path   Base property path for values (if keys are numeric or structure keywords)
	 * @param array  $array  An array of where conditions
	 * @param string $clause For multiple where clauses, tell if they are linked with "OR" or "AND"
	 * @return string
	 */
	private function buildArray($path, $array, $clause)
	{
		$sql = "";
		$first = true;
		foreach ($array as $key => $value) {
			if ($first) $first = false; else $sql .= " $clause ";
			$subclause = strtoupper($key);
			switch ($subclause) {
				case "NOT": $sql .= "NOT (" . $this->build($path, $value, "AND") . ")";  break;
				case "AND": $sql .= $this->build($path, $value, $subclause);             break;
				case "OR":  $sql .= "(" . $this->build($path, $value, $subclause) . ")"; break;
				default:    $sql .= $this->build(is_numeric($key) ? $path : $key, $value, $clause);
			}
		}
		return $sql;
	}

	//----------------------------------------------------------------------------------- buildObject
	/**
	 * Build SQL WHERE section for an object
	 *
	 * @param string $path   Base property path pointing to the object
	 * @param object $object The value is an object, which will be used for search
	 * @return string
	 */
	private function buildObject($path, $object)
	{
		if ($id = $this->sql_link->getObjectIdentifier($object)) {
			// object is linked to stored data : search with object identifier
			return $this->buildValue($path, $id);
		}
		// object is a search object : each property is a search entry, and must join table
		$this->joins->add($path);
		$array = array();
		$class = Reflection_Class::getInstanceOf(get_class($object));
		foreach ($class->accessProperties() as $property_name => $property) {
			if (isset($object->$property_name)) {
				$array[$path . "." . $property_name] = $object->$property_name; 
			}
		}
		$class->accessPropertiesDone();
		return $this->buildArray($path, $array, "AND");
	}

	//----------------------------------------------------------------------------------- buildTables
	/**
	 * Build SQL tables list, based on calculated joins for where array properties paths
	 *
	 * @return string
	 */
	protected function buildTables()
	{
		$tables = "`" . Sql_Table::classToTableName($this->class) . "` t0";
		foreach ($this->joins->getJoins() as $join) if ($join) {
			$tables .= " $join->mode JOIN `$join->foreign_table` $join->foreign_alias"
			. " ON $join->foreign_alias.$join->foreign_column = $join->master_alias.$join->master_column";
		}
		return $tables;
	}

	//------------------------------------------------------------------------------------ buildValue
	/**
	 * Build SQL WHERE section for a unique value
	 *
	 * @param string $path  search property path
	 * @param mixed  $value search property value
	 * @return string
	 */
	private function buildValue($path, $value)
	{
		$this->joins->add($path);
		list($master_path, $foreign_field) = Sql_Builder::splitPropertyPath($path);
		$column = ((!$master_path) || ($master_path === "id"))
			? ("t0.`" . $foreign_field . "`")
			: ($this->joins->getAlias($master_path) . ".`" . $foreign_field . "`");
		$expr = is_null($value)
			? " IS NULL"
			: (" " . (Sql_Value::isLike($value) ? "LIKE" : "=") . " " . Sql_Value::escape($value));
		return $column . $expr;
	}

	//------------------------------------------------------------------------------------ buildWhere
	/**
	 * Build SQL WHERE section, add add joins for search criterion
	 *
	 * @return string
	 */
	protected function buildWhere()
	{
		$sql = is_null($this->where_array) ? "" : $this->build("id", $this->where_array, "AND");
		return $sql ? " WHERE " . $sql : $sql;
	}

	//---------------------------------------------------------------------- constructSqlWhereBuilder
	/**
	 * Construct the SQL WHERE section of a query 
	 *
	 * Supported columns naming forms are :
	 * column_name : column_name must correspond to a property of class
	 * column.foreign_column : column must be a property of class, foreign_column must be a property of column's @var class
	 *
	 * @param string   $class base object class name
	 * @param array    $where_array where array expression, indices are columns names
	 * @param Sql_Link $sql_link
	 */
	protected function constructSqlWhereBuilder($class, $where_array = null, $sql_link = null)
	{
		$this->joins       = new Sql_Joins($class);
		$this->class       = $class;
		$this->sql_link    = $sql_link ? $sql_link : Dao::getDataLink();
		$this->where_array = $where_array;
	}

}
