<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Sql\Builder\Columns;
use SAF\Framework\Sql\Value;

/**
 * Group_Concat function to group values when Group_By is used
 */
class Group_Concat extends Column
{

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

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       Columns the sql query builder
	 * @param $property_path string the property path
	 * @return string
	 */
	public function toSql(Columns $builder, $property_path)
	{
		if (!isset($this->order_by)) {
			$this->order_by = [$property_path];
		}
		foreach ($this->order_by as $by_path) {
			$order_by[] = $builder->buildColumn($by_path, null, false);
		}
		if ($this->separator && ($this->separator !== ',')) {
			$separator = ' SEPARATOR ' . Value::escape($this->separator);
		}
		$sql = 'GROUP_CONCAT('
			. ($this->distinct ? 'DISTINCT ' : '')
			. $builder->buildColumn($property_path, null, false)
			. (isset($order_by) ? (' ORDER BY ' . join(SP, $order_by)) : '')
			. (isset($separator) ? $separator : '')
			. ')'
			. $this->aliasSql($builder, $property_path);
		return $sql;
	}

}
