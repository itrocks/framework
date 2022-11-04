<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Comparison;
use ITRocks\Framework\Dao\Func\Where;
use ITRocks\Framework\Trigger\Has_Condition\Run;

/**
 * For trigger on data with condition
 *
 * @display_order class_name, before_condition, after_condition
 */
trait Has_Condition
{

	//------------------------------------------------------------------------------ $after_condition
	/**
	 * @link Object
	 * @store json
	 * @var ?Where
	 */
	public ?Where $after_condition;

	//----------------------------------------------------------------------------- $before_condition
	/**
	 * @link Object
	 * @store json
	 * @var ?Where
	 */
	public ?Where $before_condition;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public string $class_name;

	//-------------------------------------------------------------------------------------- $running
	/**
	 * @link Collection
	 * @user invisible
	 * @var Run[]
	 */
	public array $running;

	//---------------------------------------------------------------------------- conditionIsNotNull
	/**
	 * Return true if $condition is Func::isNotNull()
	 *
	 * @param $condition ?Where
	 * @return boolean
	 */
	public function conditionIsNotNull(?Where $condition) : bool
	{
		return ($condition instanceof Comparison)
			&& ($condition->sign !== Comparison::EQUAL)
			&& is_null($condition->than_value);
	}

	//------------------------------------------------------------------------------- conditionIsNull
	/**
	 * Return true if $condition is Func::isNull()
	 *
	 * @param $condition ?Where
	 * @return boolean
	 */
	public function conditionIsNull(?Where $condition) : bool
	{
		return ($condition instanceof Comparison)
			&& ($condition->sign === Comparison::EQUAL)
			&& is_null($condition->than_value);
	}

	//------------------------------------------------------------------------------ verifyConditions
	/**
	 * @param $object    object
	 * @param $condition ?Where @values $after_condition, $before_condition
	 * @return boolean
	 */
	public function verifyConditions(object $object, ?Where $condition) : bool
	{
		if (!$condition) {
			return true;
		}
		if ($this->conditionIsNotNull($condition)) {
			return Dao::getObjectIdentifier($object);
		}
		if ($this->conditionIsNull($condition)) {
			return !Dao::getObjectIdentifier($object);
		}
		return Dao::count(Func::andOp([$object, $condition]), get_class($object));
	}

}
