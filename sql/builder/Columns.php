<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Column;
use ITRocks\Framework\Reflection\Annotation\Class_\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Sql;
use ITRocks\Framework\Sql\Join;
use ITRocks\Framework\Sql\Join\Joins;

/**
 * SQL columns list expression builder
 */
class Columns implements With_Build_Column
{
	use Has_Build_Column;

	//--------------------------------------------------------------------------------------- $append
	/**
	 * If set : describes what must be appended after each SQL column description
	 *
	 * - each element being a string is an expression to append to each column, ie 'DESC'
	 * - each element being an array : the main key is the expression to be appended to the properties
	 * names in the array, ie 'DESC' => ['property.path.1', 'property2')
	 *
	 * @var array|null
	 */
	private $append;

	//------------------------------------------------------------------------------- $expand_objects
	/**
	 * @var boolean
	 */
	public $expand_objects = true;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * Properties paths list
	 *
	 * @var string[]|Column[]|null
	 */
	private $properties;

	//------------------------------------------------------------------------------ $resolve_aliases
	/**
	 * @var boolean
	 */
	public $resolve_aliases = true;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct the SQL columns list section of a query
	 *
	 * @param $class_name string
	 * @param $properties string[]|Column[] properties paths list
	 * @param $joins      Joins
	 * @param $append     array appends expressions to some SQL columns
	 * - each element being a string is an expression to append to each column, ie 'DESC'
	 * - each element being an array : the main key is the expression to be appended to the properties
	 * names in the array, ie 'DESC' => ['property.path.1', 'property2')
	 */
	public function __construct(
		$class_name, array $properties = null, Joins $joins = null, array $append = null
	) {
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string
	 * @todo factorize
	 */
	public function build()
	{
		if (isset($this->properties)) {
			$first_property = true;
			$sql_columns    = '';
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
				$sql_columns .=  $this->append(is_numeric($key_path) ? $path : $key_path);
			}
		}
		elseif ($this->joins->getJoins()) {
			$class_name = $this->joins->getStartingClassName();
			/** @var $properties Reflection_Property[] */
			$properties   = [];
			$column_names = [];
			/** @noinspection PhpUnhandledExceptionInspection starting class name is always valid */
			foreach (
				(new Reflection_Class($class_name))->getProperties([T_EXTENDS, T_USE]) as $property
			) {
				$storage = Store_Name_Annotation::of($property)->value;
				$type    = $property->getType();
				if (!$property->isStatic() && !($type->isClass() && $type->isMultiple())) {
					$column_names[$property->name] = $storage;
					$properties[$property->name]   = $property;
					if ($storage !== $property->name) {
						$has_storage = true;
					}
				}
			}
			$sql_columns = '';
			foreach ($this->joins->getLinkedJoins() as $join) {
				if (isset($has_storage)) {
					/** @noinspection PhpUnhandledExceptionInspection foreign class must be valid */
					foreach (
						(new Reflection_Class($join->foreign_class))->getProperties([T_EXTENDS, T_USE])
						as $property
					) {
						if (
							!$property->isStatic()
							&& isset($column_names[$property->name])
							&& !isset($already[$property->name])
							&& !Store_Annotation::of($property)->isFalse()
						) {
							if (!$sql_columns) {
								$sql_columns .= $join->foreign_alias . '.`id`, ';
							}
							$already[$property->name] = true;
							$column_name              = $column_names[$property->name];
							$type                     = $property->getType();
							$id                       = ($type->isClass() && !$type->isDateTime()) ? 'id_' : '';
							$sql_columns .= $join->foreign_alias . DOT . BQ . $id . $column_name . BQ;
							if (($column_name !== $property->name) && $this->resolve_aliases) {
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
				/** @noinspection PhpUnhandledExceptionInspection starting class is always valid */
				if (!Link_Annotation::of(new Link_Class($this->joins->getStartingClassName()))->value) {
					$sql_columns .= 't0.`id`, ';
				}

				foreach ($column_names as $property_name => $column_name) {
					$property = $properties[$property_name];
					if (!isset($already[$property_name]) && !Store_Annotation::of($property)->isFalse()) {
						$already[$property_name] = true;
						$type                    = $property->getType();
						$id                      = ($type->isClass() && !$type->isDateTime()) ? 'id_' : '';
						$sql_columns            .= 't0.' . BQ . $id . $column_name . BQ;
						if (($column_name !== $property_name) && $this->resolve_aliases) {
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
		if ($first_property) {
			$first_property = false;
		}
		else {
			$sql_columns = ', ';
		}
		return $sql_columns . $function->toSql($this, $path);
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
		if ($first_property) {
			$first_property = false;
		}
		else {
			$sql_columns = ', ';
		}
		return $sql_columns . $this->buildColumn($path, !$this->append, false, $join);
	}

	//----------------------------------------------------------------------------- buildObjectColumn
	/**
	 * @param $path              string
	 * @param $property          Reflection_Property
	 * @param $join              Join
	 * @param $linked_join       Join
	 * @param $linked_properties Reflection_Property[]
	 * @param $first_property    boolean
	 * @return string
	 */
	private function buildObjectColumn(
		$path, Reflection_Property $property, Join $join = null, Join $linked_join = null,
		array $linked_properties = null, &$first_property = null
	) {
		$sql           = '';
		$foreign_alias = (isset($linked_join) && isset($linked_properties[$property->name]))
			? $linked_join->foreign_alias
			: $join->foreign_alias;
		$column_name = Sql\Builder::buildColumnName($property);
		if ($column_name) {
			($first_property) ? ($first_property = false) : ($sql = ', ');
			if ((substr($column_name, 0, 3) === 'id_') && Store_Annotation::of($property)->isString()) {
				$column_name = substr($column_name, 3);
			}
			$sql .= $foreign_alias . DOT . BQ . $column_name . BQ
				. (
					($this->append || !$this->resolve_aliases)
					? '' : (' AS ' . BQ . $path . ':' . $property->name . BQ)
				);
		}
		return $sql;
	}

	//---------------------------------------------------------------------------- buildObjectColumns
	/**
	 * Build columns list for an object, in order to instantiate this object when read
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $path           string
	 * @param $join           Join
	 * @param $first_property boolean
	 * @return string
	 */
	private function buildObjectColumns($path, Join $join, &$first_property)
	{
		$sql_columns = '';

		// linked join and linked properties list
		/** @noinspection PhpUnhandledExceptionInspection foreign class must be valid */
		$class = new Link_Class($join->foreign_class);
		if (Link_Annotation::of($class)->value) {
			$linked_join       = $this->joins->getLinkedJoin($join);
			$linked_properties = $class->getLinkedProperties();
		}
		else {
			$linked_join       = null;
			$linked_properties = [];
		}

		if ($this->expand_objects) {
			$properties = $this->joins->getProperties($path);
			$properties = Replaces_Annotations::removeReplacedProperties($properties);
			$properties = Store_Annotation::storedPropertiesOnly($properties);
			/** @var $properties Reflection_Property[] */
			foreach ($properties as $property) {
				$sql_columns .= $this->buildObjectColumn(
					$path, $property, $join, $linked_join, $linked_properties, $first_property
				);
			}
			($first_property) ? ($first_property = false) : ($sql_columns .= ', ');
			$foreign_alias = isset($linked_join) ? $linked_join->foreign_alias : $join->foreign_alias;
			$sql_columns  .= $foreign_alias . '.`id`'
				. (($this->append || !$this->resolve_aliases) ? '' : (' AS ' . BQ . $path . ':id' . BQ));
		}

		else {
			($first_property) ? ($first_property = false) : ($sql_columns .= ', ');
			$foreign_alias = isset($linked_join) ? $linked_join->foreign_alias : $join->foreign_alias;
			$sql_columns  .= $foreign_alias . '.`id`'
				. ($this->resolve_aliases ? (' AS ' . BQ . $path . BQ) : '');
		}

		return $sql_columns;
	}

	//----------------------------------------------------------------------------- replaceProperties
	/**
	 * If a property definition is a key of $columns' properties, then replace this definition by
	 * the functional value
	 *
	 * @param $columns Columns
	 */
	public function replaceProperties(Columns $columns)
	{
		$properties = [];
		foreach ($this->properties as $key => $property_name) {
			$property_name = is_object($property_name) ? $key : $property_name;
			if (isset($columns->properties[$property_name])) {
				$properties[$property_name] = $columns->properties[$property_name];
			}
			else {
				$properties[$key] = $property_name;
			}
		}
		$this->properties = $properties;
	}

}
