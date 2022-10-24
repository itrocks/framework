<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Builder\With_Build_Column;
use ITRocks\Framework\Sql\Value;

/**
 * The SQL CASE expression is a generic conditional expression,
 * similar to if/else statements in other languages :
 *
 * CASE WHEN condition THEN then_result
 * [ELSE else_result]
 * END
 */
class Condition extends Column
{

	//------------------------------------------------------------------------------------ $condition
	/**
	 * @var array
	 */
	public $condition;

	//---------------------------------------------------------------------------------- $else_result
	/**
	 * property_path or string or Func\Column
	 *
	 * @var Column|string
	 */
	public $else_result;

	//---------------------------------------------------------------------------------- $then_result
	/**
	 * property_path or string or Func\Column
	 *
	 * @var Column|string
	 */
	public $then_result;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Condition constructor.
	 *
	 * @param $condition   array source object for filter
	 * @param $then_result Column|string
	 * @param $else_result Column|string
	 */
	public function __construct($condition, $then_result, $else_result = null)
	{
		$this->condition   = $condition;
		$this->else_result = $else_result;
		$this->then_result = $then_result;
	}

	//-------------------------------------------------------------------------- buildConditionResult
	/**
	 * @param $class  Reflection_Class
	 * @param $result mixed property_path or string
	 * @param $builder With_Build_Column
	 * @return string
	 */
	private function buildConditionResult(
		Reflection_Class $class, $result, With_Build_Column $builder
	) {
		if ($result) {
			if ($result instanceof Column) {
				return $result->toSql($builder, '');
			}
			else {
				return (Reflection_Property::exists($class->getName(), $result)) ?
					$builder->buildColumn($result, false) : Value::escape($result);
			}
		}
		else {
			return null;
		}
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
		$starting_class    = $builder->getJoins()->getStartingClass();
		$condition_builder = (new Builder\Where(
			$starting_class->getName(), $this->condition, null, $builder->getJoins()
		))->build();

		$condition = (substr($condition_builder, 1, 5) === 'WHERE')
			? trim(substr($condition_builder, 6))
			: trim($condition_builder);
		$then_result = $this->buildConditionResult($starting_class, $this->then_result, $builder);
		$else_result = $this->buildConditionResult($starting_class, $this->else_result, $builder);

		$sql = 'CASE WHEN ' . $condition . ' THEN ' . $then_result;
		if ($else_result) {
			$sql .= ' ELSE ' . $else_result;
		}
		$sql .= ' END';

		if ($property_path) {
			$sql .= $this->aliasSql($builder, $property_path);
		}
		return $sql;
	}

}
