<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Column;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Locale\Translation;
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
use ReflectionException;

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
	 * @var array
	 */
	private array $append;

	//------------------------------------------------------------------------------- $expand_objects
	/**
	 * @var boolean
	 */
	public bool $expand_objects = true;

	//--------------------------------------------------------------------------------- $null_columns
	/**
	 * Columns to add to the clause, aliased `null`
	 *
	 * @var string[]
	 */
	public array $null_columns = [];

	//----------------------------------------------------------------------------------- $properties
	/**
	 * Properties paths list
	 *
	 * @var string[]|Column[]|null
	 */
	private ?array $properties;

	//------------------------------------------------------------------------------ $resolve_aliases
	/**
	 * @var boolean
	 */
	public bool $resolve_aliases = true;

	//------------------------------------------------------------------------------------ $translate
	/**
	 * @var ?array if null, don't translate. If array : will be filled with something looking like
	 *      ['field'] => ['t1.translation']
	 */
	public ?array $translate = null;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct the SQL columns list section of a query
	 *
	 * @param $properties string[]|Column[]|null properties paths list
	 * @param $joins      Joins
	 * @param $append     array appends expressions to some SQL columns
	 * - each element being a string is an expression to append to each column, ie 'DESC'
	 * - each element being an array : the main key is the expression to be appended to the properties
	 * names in the array, ie 'DESC' => ['property.path.1', 'property2')
	 */
	public function __construct(?array $properties, Joins $joins, array $append = [])
	{
		$this->append     = $append;
		$this->joins      = $joins;
		$this->properties = $properties;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
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
	private function append(string $property) : string
	{
		$appended = '';
		foreach ($this->append as $append_key => $append) {
			if (is_string($append)) {
				$appended .= SP . $append;
			}
			elseif (is_array($append) && in_array($property, $append)) {
				$appended .= SP . $append_key;
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
	public function build() : string
	{
		if (isset($this->properties)) {
			$class_name     = isset($this->translate) ? $this->joins->getStartingClassName() : null;
			$first_property = true;
			$sql_columns    = '';
			foreach ($this->properties as $key_path => $property_path) {
				$path = $property_path;
				if ($path instanceof Func\Column) {
					$sql_columns .= $this->buildDaoSelectFunction($key_path, $path, $first_property);
				}
				else {
					if ($class_name) try {
						$property = new Reflection_Property($class_name, $path);
						if (
							($property->getAnnotation('translate')->value === 'common')
							|| $property->getListAnnotation('values')->value
						) {
							$alias = $path;
							$path  = Translation::class
								. '(text=~' . $path . ',language=' . Q . Loc::language(). Q . ')'
								. '.translation';
							$this->translate[$path] = $alias;
						}
					}
					catch (ReflectionException) {
					}
					$join = $this->joins->add($path);
					$sql_columns .= ($join && ($join->type !== Join::LINK))
						? $this->buildObjectColumns($path, $join, $first_property)
						: $this->buildNextColumn($path, $join, $first_property);
				}
				$sql_columns .=  $this->append(is_numeric($key_path) ? $property_path : $key_path);
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
								$sql_columns .= $join->foreign_alias . '.id, ';
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
			$alias = $this->joins->rootAlias();
			if (isset($has_storage)) {
				/** @noinspection PhpUnhandledExceptionInspection starting class is always valid */
				if (!Link_Annotation::of(new Link_Class($this->joins->getStartingClassName()))->value) {
					$sql_columns .= $alias . '.id, ';
				}

				foreach ($column_names as $property_name => $column_name) {
					$property = $properties[$property_name];
					if (!isset($already[$property_name]) && !Store_Annotation::of($property)->isFalse()) {
						$already[$property_name] = true;
						$type                    = $property->getType();
						$id                      = ($type->isClass() && !$type->isDateTime()) ? 'id_' : '';
						$sql_columns            .= $alias . DOT . BQ . $id . $column_name . BQ;
						if (($column_name !== $property_name) && $this->resolve_aliases) {
							$sql_columns .= ' AS ' . BQ . $id . $property_name . BQ;
						}
						$sql_columns .= ', ';
					}
				}
				$sql_columns = substr($sql_columns, 0, -2);
			}
			else {
				$sql_columns .= $alias . '.*';
			}
		}
		else {
			$sql_columns = '*';
		}
		foreach ($this->null_columns as $null_column) {
			$sql_columns .= ', ' . $null_column . ' AS `@null`';
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
	private function buildDaoSelectFunction(
		string $path, Func\Column $function, bool &$first_property
	) : string
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
	 * @param $join           ?Join
	 * @param $first_property boolean
	 * @return string
	 */
	private function buildNextColumn(string $path, ?Join $join, bool &$first_property) : string
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
	 * @param $linked_join       ?Join
	 * @param $linked_properties Reflection_Property[]
	 * @param $first_property    boolean
	 * @return string
	 */
	private function buildObjectColumn(
		string $path, Reflection_Property $property, Join $join, ?Join $linked_join,
		array $linked_properties, bool &$first_property
	) : string
	{
		$sql           = '';
		$foreign_alias = ($linked_join && isset($linked_properties[$property->name]))
			? $linked_join->foreign_alias
			: $join->foreign_alias;
		$column_name = Sql\Builder::buildColumnName($property);
		if ($column_name) {
			$first_property ? ($first_property = false) : ($sql = ', ');
			if (str_starts_with($column_name, 'id_') && Store_Annotation::of($property)->isString()) {
				$column_name = substr($column_name, 3);
			}
			$type = $property->getType();
			$id   = ($type->isClass() && !$type->isDateTime()) ? 'id_' : '';
			$sql .= $foreign_alias . DOT . BQ . $column_name . BQ
				. (
					($this->append || !$this->resolve_aliases)
					? '' : (' AS ' . BQ . $path . ':' . $id . $property->name . BQ)
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
	private function buildObjectColumns(string $path, Join $join, bool &$first_property) : string
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
			$first_property ? ($first_property = false) : ($sql_columns .= ', ');
			$foreign_alias = $linked_join ? $linked_join->foreign_alias : $join->foreign_alias;
			if (!$properties && $class->isAbstract()) {
				$sql_columns .= $foreign_alias . DOT . BQ . 'class' . BQ
					. (
						($this->append || !$this->resolve_aliases) ? '' : (' AS ' . BQ . $path . ':class' . BQ)
					)
					. ', ';
				$sql_columns .= $foreign_alias . DOT . BQ . 'representative' . BQ
					. (
						($this->append || !$this->resolve_aliases)
						? ''
						: (' AS ' . BQ . $path . ':representative' . BQ)
					)
					. ', ';
			}
			$sql_columns  .= $foreign_alias . '.id'
				. (($this->append || !$this->resolve_aliases) ? '' : (' AS ' . BQ . $path . ':id' . BQ));
		}

		else {
			$first_property ? ($first_property = false) : ($sql_columns .= ', ');
			$foreign_alias = $linked_join ? $linked_join->foreign_alias : $join->foreign_alias;
			$sql_columns  .= $foreign_alias . '.id'
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
	public function replaceProperties(Columns $columns) : void
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
