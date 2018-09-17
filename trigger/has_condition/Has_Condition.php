<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Logical;
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
	 * @var Logical
	 */
	public $after_condition;

	//----------------------------------------------------------------------------- $before_condition
	/**
	 * @var Logical
	 */
	public $before_condition;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//-------------------------------------------------------------------------------------- $running
	/**
	 * @link Collection
	 * @user invisible
	 * @var Run[]
	 */
	public $running;

	//------------------------------------------------------------------------------ verifyConditions
	/**
	 * @param $object    object
	 * @param $condition Logical @values $after_condition, $before_condition
	 * @return boolean
	 */
	public function verifyConditions($object, Logical $condition)
	{
		return boolval(Dao::searchOne(Func::andOp([$object, $condition]), get_class($object)));
	}

}
