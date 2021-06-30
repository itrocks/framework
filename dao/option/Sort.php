<?php
namespace ITRocks\Framework\Dao\Option;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Mapper\Comparator;
use ITRocks\Framework\Reflection\Annotation\Class_\Representative_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Sort_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * A DAO sort option
 */
class Sort implements Option
{
	use Has_In;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string|null
	 */
	public ?string $class_name =  null;

	//-------------------------------------------------------------------------------------- $columns
	/**
	 * Columns names for objects collection sorting
	 *
	 * @var string[]|Reverse[]|null
	 */
	public ?array $columns = null;

	//-------------------------------------------------------------------------------------- $reverse
	/**
	 * These are columns names which use reverse sort
	 *
	 * @var string[]|null
	 */
	public ?array $reverse;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct a DAO sort option using the columns names or paths used to sort data using current
	 * data link when readAll() and search()
	 *
	 * @example
	 * $option = new Dao_Sort_Option(['first_name', 'last_name', 'city.country.name'));
	 * // Please prefer using this equivalent for standard calls, ie in this standard use example :
	 * $users = Dao::readAll(
	 *   'ITRocks\Framework\User',
	 *   Dao::sort(['first_name', 'last_name', 'city.country.name')));
	 * );
	 * @param $columns string|string[]|Reverse[] a single or several column names, or a class name
	 * to apply each column name can be followed by ' reverse' into the string for reverse order sort
	 * If null, the value of annotations 'sort' or 'representative' of the class will be taken.
	 */
	public function __construct(array|string $columns = null)
	{
		if (is_string($columns) && ctype_upper($columns[0])) {
			$this->applyClassName($columns);
		}
		elseif (isset($columns)) {
			$this->columns = is_array($columns) ? $columns : [$columns];
			$this->calculateReverse();
		}
	}

	//--------------------------------------------------------------------------------- addSortColumn
	/**
	 * @param $property_path      string
	 * @param $sort_columns_count integer number of sort columns to keep after adding
	 */
	public function addSortColumn(string $property_path, int $sort_columns_count = 3)
	{
		if (in_array($property_path, $this->columns)) {
			unset($this->columns[array_search($property_path, $this->columns)]);
		}
		array_unshift($this->columns, $property_path);
		$this->columns = array_slice($this->columns, 0, $sort_columns_count);
		// remove reverse of removed columns
		foreach ($this->reverse as $key => $property_path) {
			if (!in_array($property_path, $this->columns)) {
				unset($this->reverse[$key]);
			}
		}
		$this->reverse = array_values($this->reverse);
	}

	//-------------------------------------------------------------------------------- applyClassName
	/**
	 * Apply class name : if constructor was called without columns, this will initialize columns list
	 *
	 * This applies default column names if there was no default class name, or if class name changed,
	 * or if there were no column names.
	 *
	 * @noinspection PhpDocMissingThrowsInspection $class_name must be valid
	 * @param $class_name string
	 */
	private function applyClassName(string $class_name)
	{
		if (
			isset($class_name)
			&& ($class_name !== $this->class_name)
			&& (isset($this->class_name) || !isset($this->columns))
		) {
			$class_name       = Builder::className($class_name);
			$this->class_name = $class_name;
			/** @noinspection PhpUnhandledExceptionInspection $class_name must be valid */
			$class         = new Reflection_Class($class_name);
			$this->columns = Sort_Annotation::of($class)->values()
				?: Representative_Annotation::of($class)->values();
			$this->calculateReverse();
		}
	}

	//------------------------------------------------------------------------------ calculateReverse
	/**
	 * Calculate reverse on columns that are not already reverse
	 */
	private function calculateReverse()
	{
		if (isset($this->columns) && !isset($this->reverse)) {
			$this->reverse = [];
			foreach ($this->columns as $key => $column_name) {
				if (is_string($column_name) && ($column_name[0] === '-')) {
					$column_name                 = substr($column_name, 1);
					$this->reverse[$column_name] = $column_name;
					$this->columns[$key]         = $column_name;
				}
				elseif (str_contains(SP . $column_name . SP, SP . 'reverse' . SP)) {
					$column_name = trim(str_replace(SP . 'reverse' . SP, '', SP . $column_name . SP));
					$this->reverse[$column_name] = $column_name;
					$this->columns[$key]         = $column_name;
				}
			}
		}
	}

	//------------------------------------------------------------------------------------ getColumns
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string|null the contextual class name :
	 *                    needed if the constructor was called without columns
	 * @return string[] the column names
	 */
	public function getColumns(string $class_name = null) : array
	{
		if (isset($class_name)) {
			$this->applyClassName($class_name);
		}
		$columns = [];
		foreach ($this->columns as $property_name) {
			/** @noinspection PhpUnhandledExceptionInspection must be valid */
			$property = new Reflection_Property($class_name ?: $this->class_name, $property_name);
			$type     = $property->getType();
			if ($type->isClass() && !$type->isDateTime()) {
				foreach ((new static($type->getElementTypeAsString()))->getColumns() as $sub_column) {
					$column    = $property_name . DOT . $sub_column;
					$columns[] = $column;
					if (in_array($property_name, $this->reverse) && !in_array($column, $this->reverse)) {
						$this->reverse[] = $column;
					}
				}
			}
			else {
				$columns[] = $property_name;
			}
		}
		return $columns;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string|null the contextual class name :
	 *                    needed if the constructor was called without columns
	 * @return Reflection_Property[] the properties
	 */
	public function getProperties(string $class_name = null) : array
	{
		$properties = [];
		foreach ($this->getColumns($class_name) as $column) {
			/** @noinspection PhpUnhandledExceptionInspection column from valid class */
			$properties[$column] = new Reflection_Property($this->class_name, $column);
		}
		return $properties;
	}

	//------------------------------------------------------------------------------------- isReverse
	/**
	 * Returns true if the property path has a reverse sort
	 *
	 * @param $property_path string
	 * @return boolean
	 */
	public function isReverse(string $property_path) : bool
	{
		if (in_array($property_path, $this->reverse)) {
			return true;
		}
		foreach ($this->columns as $column) {
			if (($column instanceof Reverse) && ($column->column === $property_path)) {
				return true;
			}
		}
		return false;
	}

	//----------------------------------------------------------------------------------- sortObjects
	/**
	 * Sort a collection of objects using current sort columns configuration
	 *
	 * @param $objects object[]
	 * @return object[]
	 */
	public function sortObjects(array &$objects) : array
	{
		$comparator                     = new Comparator($this->class_name, $this->columns);
		$comparator->use_compare_method = false;
		$comparator->sort($objects);
		return $objects;
	}

}
