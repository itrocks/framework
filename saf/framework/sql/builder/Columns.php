<?php
namespace SAF\Framework\Sql\Builder;

use SAF\Framework\Builder;
use SAF\Framework\Dao\Func;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Sql;
use SAF\Framework\Sql\Join\Joins;
use SAF\Framework\Sql\Join;

/**
 * SQL columns list expression builder
 */
class Columns
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
	 * @var Joins
	 */
	private $joins;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * Properties paths list
	 *
	 * @var string[]|Func[]
	 */
	private $properties;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct the SQL columns list section of a query
	 *
	 * @param $class_name string
	 * @param $properties string[] properties paths list
	 * @param $joins      Joins
	 * @param $append     mixed[] appends expressions to some SQL columns
	 * - each element being a string is an expression to append to each column, ie 'DESC'
	 * - each element being an array : the main key is the expression to be appended to the properties
	 * names in the array, ie 'DESC' => ['property.path.1', 'property2')
	 */
	public function __construct($class_name, $properties, Joins $joins = null, $append = null)
	{
		$this->joins      = $joins ? $joins : new Joins($class_name);
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
				if ($path instanceof Func\Column) {
					$sql_columns .= $this->buildDaoSelectFunction($key_path, $path, $first_property);
				}
				else {
					$join = $this->joins->add($path);
					$sql_columns .= ($join && ($join->type !== Join::LINK))
						? $this->buildObjectColumns($path, $join, $first_property)
						: $this->buildNextColumn($path, $join, $first_property);
				}
				$sql_columns .=  $this->append($path);
			}
		}
		elseif ($this->joins->getJoins()) {
			$class_name = Builder::className($this->joins->getStartingClassName());
			/** @var $properties Reflection_Property[] */
			$properties = [];
			$column_names = [];
			foreach (
				(new Reflection_Class($class_name))->getProperties([T_EXTENDS, T_USE]) as $property
			) {
				$storage = $property->getAnnotation('storage')->value;
				$type = $property->getType();
				if (!$property->isStatic() && !($type->isClass() && $type->isMultiple())) {
					$column_names[$property->name] = $storage;
					$properties[$property->name] = $property;
					if ($storage !== $property->name) {
						$has_storage = true;
					}
				}
			}
			$sql_columns = '';
			foreach ($this->joins->getLinkedJoins() as $join) {
				if (isset($has_storage)) {
					foreach (
						(new Reflection_Class($join->foreign_class))->getProperties([T_EXTENDS, T_USE]) as $property
					) {
						if (
							!$property->isStatic()
							&& isset($column_names[$property->name])
							&& !isset($already[$property->name])
						) {
							$column_name = $column_names[$property->name];
							$id = $property->getType()->isClass() ? 'id_' : '';
							$already[$property->name] = true;
							$sql_columns .= $join->foreign_alias . '.' . BQ . $id . $column_name . BQ;
							if ($column_name !== $property->name) {
								$sql_columns .= ' AS ' . BQ . $id . $property->name . BQ;
							}
							$sql_columns .= ', ';
						}
					}
				}
				else {
					$sql_columns .= $join->foreign_alias . '.*, ';
				}
			}
			// the main table comes last, as fields with the same name must have the main value (ie 'id')
			if (isset($has_storage)) {
				$sql_columns .= 't0.id, ';
				foreach ($column_names as $property_name => $column_name) {
					if (!isset($already[$property_name])) {
						$already[$property_name] = true;
						$id = $properties[$property_name]->getType()->isClass() ? 'id_' : '';
						$sql_columns .= 't0.' . BQ . $id . $column_name . BQ;
						if ($column_name !== $property_name) {
							$sql_columns .= ' AS ' . BQ . $id . $property_name . BQ;
						}
						$sql_columns .= ', ';
					}
				}
				$sql_columns = substr($sql_columns, 0, -2);
			}
			else {
				$sql_columns .= 't0.*';
			}
		}
		else {
			$sql_columns = '*';
		}
		return $sql_columns;
	}

	//----------------------------------------------------------------------------------- buildColumn
	/**
	 * @param $path string
	 * @param $join Join
	 * @param $as   boolean
	 * @return string
	 */
	public function buildColumn($path, $join = null, $as = true)
	{
		if (!isset($join)) {
			$join = $this->joins->add($path);
		}
		list($master_path, $column_name) = Sql\Builder::splitPropertyPath($path);
		if (!isset($join)) {
			$join = $this->joins->getJoin($master_path);
		}
		return
			($join ? ($join->foreign_alias . '.' . BQ . $column_name . BQ) : ('t0.' . BQ . $path . BQ))
			. (($as && $column_name !== $path) ? (' AS ' . BQ . $path . BQ) : false);
	}

	//------------------------------------------------------------------------------- buildNextColumn
	/**
	 * Build SQL query section for a single column
	 *
	 * @param $path           string the past of the matching property
	 * @param $join           Join
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
	 * @param $function       Func\Column
	 * @param $first_property boolean
	 * @return string
	 */
	private function buildDaoSelectFunction($path, Func\Column $function, &$first_property)
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
	 * @param $join Join
	 * @param $first_property boolean
	 * @return string
	 */
	private function buildObjectColumns($path, Join $join, &$first_property)
	{
		$sql_columns = '';
		foreach ($this->joins->getProperties($path) as $property) {
			$column_name = Sql\Builder::buildColumnName($property);
			if ($column_name) {
				if ($first_property) $first_property = false; else $sql_columns .= ', ';
				$sql_columns .= $join->foreign_alias . '.' . BQ . $column_name . BQ
					. ($this->append ? '' : (' AS ' . BQ . $path . ':' . $property->name . BQ));
			}
		}
		if ($first_property) $first_property = false; else $sql_columns .= ', ';
		$sql_columns .= $join->foreign_alias . '.id'
			. ($this->append ? '' : (' AS ' . BQ . $path . ':id' . BQ));
		return $sql_columns;
	}

	//-------------------------------------------------------------------------------------- getJoins
	/**
	 * @return Joins
	 */
	public function getJoins()
	{
		return $this->joins;
	}

}
