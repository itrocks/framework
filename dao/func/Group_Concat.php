<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Sql\Builder\With_Build_Column;
use ITRocks\Framework\Sql\Value;

/**
 * Group_Concat function to group values when Group_By is used
 */
class Group_Concat extends Column
{

	//--------------------------------------------------------------------------------------- $column
	/**
	 * The property path or Func\Column to which the concatenation applies
	 * Default (if not set) will be the property path associated with the call of this function
	 *
	 * @var Column|string
	 */
	public $column;

	//------------------------------------------------------------------------------------- $distinct
	/**
	 * @var boolean
	 */
	public $distinct = true;

	//------------------------------------------------------------------------------------- $order_by
	/**
	 * Default will be the property path
	 *
	 * @var string[]
	 */
	public $order_by;

	//------------------------------------------------------------------------------------ $separator
	/**
	 * Separator for the concatenation, if not a comma (which is the default).
	 *
	 * @var string
	 */
	public $separator;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $column    Column|string Property path or Func\Column.
	 *                   Default will be the associated property path.
	 * @param $separator string Separator for the concat @default ,
	 */
	public function __construct($column = null, $separator = null)
	{
		$this->column    = $column;
		$this->separator = $separator;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       With_Build_Column the sql query builder
	 * @param $property_path string the property path
	 * @return string
	 */
	public function toSql(With_Build_Column $builder, $property_path)
	{
		$group_concat_property = $property_path;

		if ($this->column) {
			$group_concat_property = ($this->column instanceof Column)
				? $this->column->toSql($builder, null)
				: $this->column;
		}

		$group_concat_property = Reflection_Property::exists(
			$builder->getJoins()->getStartingClassName(), $group_concat_property
		)
			? $builder->buildColumn($group_concat_property, false, true)
			: $group_concat_property;

		if (isset($this->order_by)) {
			$order_by = [];
			foreach ($this->order_by as $by_path) {
				$order_by[] = $builder->buildColumn($by_path, false, true);
			}
		}
		else {
			$order_by = [$group_concat_property];
		}

		if ($this->separator && ($this->separator !== ',')) {
			$separator = ' SEPARATOR ' . Value::escape($this->separator);
		}

		$sql = 'GROUP_CONCAT('
			. ($this->distinct ? 'DISTINCT ' : '')
			. $group_concat_property
			. ($order_by ? ' ORDER BY ' . join(', ', $order_by) : '')
			. (isset($separator) ? $separator : '')
			. ')'
			. $this->aliasSql($builder, $property_path);
		return $sql;
	}

}
