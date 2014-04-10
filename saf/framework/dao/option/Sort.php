<?php
namespace SAF\Framework\Dao\Option;

use SAF\Framework\Builder;
use SAF\Framework\Dao\Option;
use SAF\Framework\Reflection\Reflection_Class;

/**
 * A DAO sort option
 */
class Sort implements Option
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//-------------------------------------------------------------------------------------- $columns
	/**
	 * Columns names for objects collection sorting
	 *
	 * @var string[]
	 */
	public $columns;

	//-------------------------------------------------------------------------------------- $reverse
	/**
	 * These are columns names which use reverse sort
	 *
	 * @var string[]
	 */
	public $reverse;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct a DAO sort option using the columns names or paths used to sort data using current
	 * data link when readAll() and search()
	 *
	 * @example
	 * $option = new Dao_Sort_Option(['first_name', 'last_name', 'city.country.name'));
	 * // Please prefer using this equivalent for standard calls, ie in this standard use example :
	 * $users = Dao::readAll(
	 *   'SAF\Framework\User',
	 *   Dao::sort(['first_name', 'last_name', 'city.country.name')));
	 * );
	 *
	 * @param $columns string|string[] a single or several column names, or a class name to apply
	 * each column name can be followed by ' reverse' into the string for reverse order sort
	 * If null, the value of annotations 'sort' or 'representative' of the class will be taken.
	 */
	public function __construct($columns = null)
	{
		if (is_string($columns) && (($columns[0] >= 'A') && ($columns[0] <= 'Z'))) {
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
	public function addSortColumn($property_path, $sort_columns_count = 3)
	{
		if (in_array($property_path, $this->columns)) {
			unset($this->columns[array_search($property_path, $this->columns)]);
		}
		array_unshift($this->columns, $property_path);
		$this->columns = array_slice($this->columns, 0, $sort_columns_count);
	}

	//-------------------------------------------------------------------------------- applyClassName
	/**
	 * Apply class name : if constructor was called without columns, this will initialize columns list
	 *
	 * This applies default column names if there was no default class name, or if class name changed,
	 * or if there were no column names.
	 *
	 * @param $class_name string
	 */
	private function applyClassName($class_name)
	{
		if (
			isset($class_name)
			&& ($class_name != $this->class_name)
			&& (isset($this->class_name) || !isset($this->columns))
		) {
			$class_name = Builder::className($class_name);
			$this->class_name = $class_name;
			$columns = (new Reflection_Class($class_name))->getAnnotation('sort')->value;
			if (!$columns) {
				$columns = (new Reflection_Class($class_name))->getAnnotation('representative')->value;
			}
			$this->columns = $columns;
			$this->calculateReverse();
		}
	}

	//----------------------------------------------------------------------------------- $class_name
	private function calculateReverse()
	{
		if (isset($this->columns) && !isset($this->reverse)) {
			$this->reverse = [];
			foreach ($this->columns as $key => $column_name) {
				if (strpos(SP . $column_name . SP, SP . 'reverse' . SP) !== false) {
					$column_name = trim(str_replace(SP . 'reverse' . SP, '', SP . $column_name . SP));
					$this->reverse[$column_name] = $column_name;
					$this->columns[$key] = $column_name;
				}
			}
		}
	}

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @param $class_name string the contextual class name :
	 * needed if the constructor was called without columns
	 * @return string[] the column names
	 */
	public function getColumns($class_name = null)
	{
		if (isset($class_name)) {
			$this->applyClassName($class_name);
		}
		return $this->columns;
	}

}
