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
	 * @var ?Column
	 */
	public ?Column $column = null;

	//------------------------------------------------------------------------------------- $distinct
	/**
	 * @var boolean
	 */
	public bool $distinct = true;

	//------------------------------------------------------------------------------------- $order_by
	/**
	 * Default will be the property path
	 *
	 * @var string[]
	 */
	public array $order_by;

	//------------------------------------------------------------------------------------ $separator
	/**
	 * Separator for the concatenation, if not a comma (which is the default).
	 *
	 * @var string
	 */
	public string $separator;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $separator Column|string Separator for the concat @default ,
	 * @param $column    Column|null
	 */
	public function __construct(Column|string $separator = '', Column $column = null)
	{
		$this->column    = ($separator instanceof Column) ? $separator : $column;
		$this->separator = ($separator instanceof Column) ? '' : $separator;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       With_Build_Column the sql query builder
	 * @param $property_path string the property path
	 * @return string
	 */
	public function toSql(With_Build_Column $builder, string $property_path) : string
	{
		$alias_property_path = $property_path;
		if ($this->column instanceof Column) {
			$property_path = lParse($this->column->toSql($builder, $property_path), ' AS ');
		}

		$group_concat_property = Reflection_Property::exists(
			$builder->getJoins()->getStartingClassName(), $property_path
		)
			? $builder->buildColumn($property_path, false, true)
			: $property_path;

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

		return 'GROUP_CONCAT('
			. ($this->distinct ? 'DISTINCT ' : '')
			. $group_concat_property
			. ($order_by ? ' ORDER BY ' . join(', ', $order_by) : '')
			. ($separator ?? '')
			. ')'
			. $this->aliasSql($builder, $alias_property_path);
	}

}
