<?php
namespace SAF\Framework;

/**
 * A DAO sort option
 */
class Dao_Sort_Option implements Dao_Option
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
	 * $option = new Dao_Sort_Option(array("first_name", "last_name", "city.country.name"));
	 * // Please prefer using this equivalent for standard calls, ie in this standard use example :
	 * $users = Dao::readAll(
	 *   'SAF\Framework\User',
	 *   Dao::sort(array("first_name", "last_name", "city.country.name")));
	 * );
	 *
	 * @param $columns string|string[] a single or several column names, or a class name to apply
	 * each column name can be followed by " reverse" into the string for reverse order sort
	 * If null, the value of annotations "sort" or "representative" of the class will be taken.
	 */
	public function __construct($columns = null)
	{
		if (is_string($columns) && (($columns[0] >= "A") && ($columns[0] <= "Z"))) {
			$this->applyClassName($columns);
		}
		elseif (isset($columns)) {
			$this->columns = is_array($columns) ? $columns : array($columns);
			$this->calculateReverse();
		}
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
			$columns = (new Reflection_Class($class_name))->getAnnotation("sort")->value;
			if (!$columns) {
				$columns = (new Reflection_Class($class_name))->getAnnotation("representative")->value;
			}
			$this->columns = $columns;
			$this->calculateReverse();
		}
	}

	//----------------------------------------------------------------------------------- $class_name
	private function calculateReverse()
	{
		if (isset($this->columns) && !isset($this->reverse)) {
			$this->reverse = array();
			foreach ($this->columns as $key => $column_name) {
				if ($i = strpos(" " . $column_name . " ", " reverse ")) {
					$i --;
					$this->reverse[$column_name] = true;
					$this->columns[$key] = substr($column_name, 0, $i) . substr($column_name, $i + 8);
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
