<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Feature\List_\Summary_Builder;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Value;

/**
 * Dao IN function
 */
class In implements Negate, Where
{
	use Has_To_String;

	//--------------------------------------------------------------------------------------- $not_in
	/**
	 * false : 'NOT IN', true : 'IN'
	 *
	 * @var boolean
	 */
	public bool $in;

	//--------------------------------------------------------------------------------------- $values
	/**
	 * @var array
	 */
	public array $values;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $values array|null
	 * @param $in     boolean|null
	 */
	public function __construct(array $values = null, bool $in = null)
	{
		if (isset($values)) $this->values = $values;
		if (isset($in))     $this->in     = $in;
	}

	//---------------------------------------------------------------------------------------- negate
	/**
	 * Negate the Dao function
	 */
	public function negate() : void
	{
		$this->in = !$this->in;
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
	public function toHuman(Summary_Builder $builder, string $property_path, string $prefix = '')
		: string
	{
		$summary = '';
		if ($this->values) {
			[$translation_delimiter] = $builder->getTranslationDelimiters();

			$values = [];
			foreach ($this->values as $value) {
				$values[] = $builder->buildScalar($value, $property_path, Summary_Builder::SUB_TRANSLATE);
			}
			sort($values);
			$values = join(', ', $values);

			$summary = $translation_delimiter . str_replace(
				['$property', '$values'],
				[$builder->buildColumn($property_path, $prefix, Summary_Builder::SUB_TRANSLATE), $values],
				Loc::tr($this->in ? '$property is one of ($values)' : '$property is not one of ($values)')
			) . $translation_delimiter;
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
	public function toSql(Builder\Where $builder, string $property_path, string $prefix = '') : string
	{
		$sql = '';
		if ($this->values) {
			if (count($this->values) === 1) {
				$comparison_sign = $this->in ? Comparison::EQUAL : Comparison::NOT_EQUAL;
				$comparison      = new Comparison($comparison_sign, reset($this->values));
				return $comparison->toSql($builder, $property_path, $prefix);
			}
			// 1st we should call buildWhereColumn() to be able to call getProperty() after
			$column   = $builder->buildWhereColumn($property_path, $prefix);
			$property = $builder->getProperty($property_path);
			if (
				$property
				&& $property->getType()->isMultipleString()
				&& Values::of($property)?->values
			) {
				$parts = [];
				foreach($this->values as $value) {
					$parts[] = new In_Set($value);
				}
				$where = Func::orOp($parts);
				if (!$this->in) {
					$where = Func::notOp($where);
				}
				$sql .= $where->toSql($builder, $property_path, $prefix);
			}
			else {
				$first = true;
				$sql   = $column . ($this->in ? '' : ' NOT') . ' IN (';
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
