<?php
namespace SAF\Framework;

/**
 * SQL columns list expression builder
 */
class Sql_Columns_Builder
{

	//--------------------------------------------------------------------------------------- $append
	/**
	 * If set : describes what must be appended after each SQL column description
	 *
	 * - each element being a string is an expression to append to each column, ie 'DESC'
	 * - each element being an array : the main key is the expression to be appended to the properties
	 * names in the array, ie 'DESC' => ['property.path.1', 'property2')
	 *
	 * @var mixed[]|null
	 */
	private $append;

	//---------------------------------------------------------------------------------------- $joins
	/**
	 * Sql joins
	 *
	 * @var Sql_Joins
	 */
	private $joins;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * Properties paths list
	 *
	 * @var string[]
	 */
	private $properties;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct the SQL columns list section of a query
	 *
	 * @param $class_name string
	 * @param $properties string[] properties paths list
	 * @param $joins      Sql_Joins
	 * @param $append     mixed[] appends expressions to some SQL columns
	 * - each element being a string is an expression to append to each column, ie 'DESC'
	 * - each element being an array : the main key is the expression to be appended to the properties
	 * names in the array, ie 'DESC' => ['property.path.1', 'property2')
	 */
	public function __construct($class_name, $properties, Sql_Joins $joins = null, $append = null)
	{
		$this->joins      = $joins ? $joins : new Sql_Joins($class_name);
		$this->properties = $properties;
		$this->append     = $append;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->build();
	}

	//---------------------------------------------------------------------------------------- append
	/**
	 * Uses $this->append to append expressions to the end of the SQL column description
	 *
	 * @param $property string property path
	 * @return string the SQL expression to be appended to the column name (with needed spaces)
	 */
	private function append($property)
	{
		$appended = '';
		if (isset($this->append)) {
			foreach ($this->append as $append_key => $append) {
				if (is_string($append)) {
					$appended .= SP . $append;
				}
				elseif (is_array($append) && in_array($property, $append)) {
					$appended .= SP . $append_key;
				}
			}
		}
		return $appended;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * Build the columns list, based on properties paths
	 *
	 * @return string
	 */
	public function build()
	{
		if (isset($this->properties)) {
			$sql_columns = '';
			$first_property = true;
			foreach ($this->properties as $key_path => $path) {
				if ($path instanceof Dao_Column_Function) {
					$sql_columns .= $this->buildDaoSelectFunction($key_path, $path, $first_property);
				}
				else {
					$join = $this->joins->add($path);
					$sql_columns .= ($join && ($join->type !== Sql_Join::LINK))
						? $this->buildObjectColumns($path, $join, $first_property)
						: $this->buildNextColumn($path, $join, $first_property);
				}
				$sql_columns .=  $this->append($path);
			}
		} elseif ($this->joins->getJoins()) {
			$sql_columns = '';
			foreach ($this->joins->getLinkedJoins() as $join) {
				$sql_columns .= $join->foreign_alias . '.*, ';
			}
			// the main table comes last, as fields with the same name must have the main value (ie 'id')
			$sql_columns .= 't0.*';
		} else {
			$sql_columns = '*';
		}
		return $sql_columns;
	}

	//----------------------------------------------------------------------------------- buildColumn
	/**
	 * @param $path string
	 * @param $join Sql_Join
	 * @param $as   boolean
	 * @return string
	 */
	public function buildColumn($path, $join = null, $as = true)
	{
		if (!isset($join)) {
			$join = $this->joins->add($path);
		}
		list($master_path, $column_name) = Sql_Builder::splitPropertyPath($path);
		if (!isset($join)) {
			$join = $this->joins->getJoin($master_path);
		}
		return ($join ? ($join->foreign_alias . '.`' . $column_name . '`') : ('t0.`' . $path . '`'))
			. (($as && $column_name !== $path) ? ' AS `' . $path . '`' : false);
	}

	//------------------------------------------------------------------------------- buildNextColumn
	/**
	 * Build SQL query section for a single column
	 *
	 * @param $path           string the past of the matching property
	 * @param $join           Sql_Join
	 * @param $first_property boolean
	 * @return string
	 */
	private function buildNextColumn($path, $join, &$first_property)
	{
		$sql_columns = '';
		if ($first_property) $first_property = false; else $sql_columns = ', ';
		return $sql_columns . $this->buildColumn($path, $join, !$this->append);
	}

	//------------------------------------------------------------------------ buildDaoSelectFunction
	/**
	 * @param $path           string
	 * @param $function       Dao_Column_Function
	 * @param $first_property boolean
	 * @return string
	 */
	private function buildDaoSelectFunction($path, Dao_Column_Function $function, &$first_property)
	{
		$sql_columns = '';
		if ($first_property) $first_property = false; else $sql_columns = ', ';
		return $sql_columns . $function->toSql($this, $path);
	}

	//---------------------------------------------------------------------------- buildObjectColumns
	/**
	 * Build columns list for an object, in order to instantiate this object when read
	 *
	 * @param $path string
	 * @param $join Sql_Join
	 * @param $first_property boolean
	 * @return string
	 */
	private function buildObjectColumns($path, Sql_Join $join, &$first_property)
	{
		$sql_columns = '';
		foreach ($this->joins->getProperties($path) as $property) {
			$column_name = Sql_Builder::buildColumnName($property);
			if ($column_name) {
				if ($first_property) $first_property = false; else $sql_columns .= ', ';
				$sql_columns .= $join->foreign_alias . '.`' . $column_name . '`'
					. ($this->append ? '' : (' AS `' . $path . ':' . $property->name . '`'));
			}
		}
		if ($first_property) $first_property = false; else $sql_columns .= ', ';
		$sql_columns .= $join->foreign_alias . '.id'
			. ($this->append ? '' : (' AS `' . $path . ':id`'));
		return $sql_columns;
	}

	//-------------------------------------------------------------------------------------- getJoins
	/**
	 * @return Sql_Joins
	 */
	public function getJoins()
	{
		return $this->joins;
	}

}
