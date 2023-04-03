<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Comparison;
use ITRocks\Framework\Dao\Func\Where;
use ITRocks\Framework\Reflection\Attribute\Class_\Display_Order;
use ITRocks\Framework\Reflection\Attribute\Property\Component;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Trigger\Has_Condition\Run;

/**
 * For trigger on data with condition
 */
#[Display_Order('class_name', 'before_condition', 'after_condition')]
trait Has_Condition
{

	//------------------------------------------------------------------------------ $after_condition
	#[Store(Store::JSON)]
	public ?Where $after_condition;

	//----------------------------------------------------------------------------- $before_condition
	#[Store(Store::JSON)]
	public ?Where $before_condition;

	//----------------------------------------------------------------------------------- $class_name
	public string $class_name;

	//-------------------------------------------------------------------------------------- $running
	/** @var Run[] */
	#[Component, User(User::INVISIBLE)]
	public array $running;

	//---------------------------------------------------------------------------- conditionIsNotNull
	/** Returns true if $condition is Func::isNotNull() */
	public function conditionIsNotNull(?Where $condition) : bool
	{
		return ($condition instanceof Comparison)
			&& ($condition->sign !== Comparison::EQUAL)
			&& is_null($condition->than_value);
	}

	//------------------------------------------------------------------------------- conditionIsNull
	/** Returns true if $condition is Func::isNull() */
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
