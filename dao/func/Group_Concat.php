<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Value;

/**
 * Group_Concat function to group values when Group_By is used
 */
class Group_Concat extends Column
{
	/**
	 * Default will be property path
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
	 * @var string
	 */
	public $separator;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($column = null, $separator = null)
	{
		$this->column    = $column;
		$this->separator = $separator;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       Builder\Columns the sql query builder
	 * @param $property_path string the property path
	 * @return string
	 */
	public function toSql(Builder\Columns $builder, $property_path)
	{
		$group_concat_property = $property_path;
		if ($this->column) {
			if ($this->column instanceof Column) {
				$group_concat_property = $this->column->toSql($builder, null);
			}
			else {
				$group_concat_property = $this->column;
			}
		}

		$group_concat_property = (Reflection_Property::exists(
			$builder->getJoins()->getStartingClassName(), $group_concat_property
		))
			? $builder->buildColumn($group_concat_property, false, true)
			: $group_concat_property;

		if (!isset($this->order_by)) {
			$this->order_by = [$group_concat_property];
		}

		if ($this->separator && ($this->separator !== ',')) {
			$separator = ' SEPARATOR ' . Value::escape($this->separator);
		}

		$sql = 'GROUP_CONCAT('
			. ($this->distinct ? 'DISTINCT ' : '')
			. $group_concat_property
			. (isset($order_by) ? (' ORDER BY ' . join(SP, $order_by)) : '')
			. (isset($separator) ? $separator : '')
			. ')'
			. $this->aliasSql($builder, $property_path);
		return $sql;
	}

}
