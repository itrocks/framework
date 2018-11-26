<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Value;
use ITRocks\Framework\Widget\List_\Summary_Builder;

/**
 * Dao IN function
 */
class In implements Negate, Where
{
	use Has_To_String;

	//--------------------------------------------------------------------------------------- $not_in
	/**
	 * If true, then this is a 'NOT IN' instead of a 'IN'
	 *
	 * @var boolean
	 */
	public $not_in;

	//--------------------------------------------------------------------------------------- $values
	/**
	 * @var mixed[]
	 */
	public $values;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $values array
	 * @param $not_in boolean
	 */
	public function __construct(array $values = null, $not_in = false)
	{
		if (isset($values)) $this->values = $values;
		if (isset($not_in)) $this->not_in = $not_in;
	}

	//---------------------------------------------------------------------------------------- negate
	/**
	 * Negate the Dao function
	 */
	public function negate()
	{
		$this->not_in = !$this->not_in;
	}

	//--------------------------------------------------------------------------------------- toHuman
	/**
	 * Returns the Dao function as Human readable string
	 *
	 * @param $builder       Summary_Builder the sql query builder
	 * @param $property_path string the property path
	 * @param $prefix        string column name prefix
	 * @return string
	 */
	public function toHuman(Summary_Builder $builder, $property_path, $prefix = '')
	{
		$summary = '';
		if ($this->values) {
			list($translation_delimiter) = $builder->getTranslationDelimiters();

			$values = [];
			foreach ($this->values as $value) {
				$values[] = $builder->buildScalar($value, $property_path, $builder::SUB_TRANSLATE);
			}
			sort($values);
			$values = join(', ', $values);

			$summary = $translation_delimiter . str_replace(
				['$property', '$values'],
				[$builder->buildColumn($property_path, $prefix, $builder::SUB_TRANSLATE), $values],
				Loc::tr(
					$this->not_in ? '$property is not one of ($values)' : '$property is one of ($values)'
				)
			) . $translation_delimiter ;
		}
		return $summary;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       Builder\Where the sql query builder
	 * @param $property_path string the property path
	 * @param $prefix        string column name prefix
	 * @return string
	 */
	public function toSql(Builder\Where $builder, $property_path, $prefix = '')
	{
		$sql = '';
		if ($this->values) {
			if (count($this->values) === 1) {
				$comparison_sign = $this->not_in ? Comparison::NOT_EQUAL : Comparison::EQUAL;
				$comparison      = new Comparison($comparison_sign, reset($this->values));
				return $comparison->toSql($builder, $property_path, $prefix);
			}
			// 1st we should call buildWhereColumn() to be able to call getProperty() after
			$column   = $builder->buildWhereColumn($property_path, $prefix);
			$property = $builder->getProperty($property_path);
			if (
				$property
				&& $property->getType()->isMultipleString()
				&& $property->getListAnnotation('values')->values()
			) {
				$parts = [];
				foreach($this->values as $value) {
					$parts[] = new In_Set($value);
				}
				$where = Func::orOp($parts);
				if ($this->not_in) {
					$where = Func::notOp($where);
				}
				$sql .= $where->toSql($builder, $property_path, $prefix);
			}
			else {
				$first = true;
				$sql   = $column . ($this->not_in ? ' NOT' : '') . ' IN (';
				foreach ($this->values as $value) {
					if ($first) {
						$first = false;
					}
					else {
						$sql .= ', ';
					}
					$sql .= Value::escape($value, false, $builder->getProperty($property_path));
				}
				$sql .= ')';
			}
		}
		return $sql;
	}

}
