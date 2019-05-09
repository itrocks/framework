<?php
namespace ITRocks\Framework\Trigger\Change;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link\Write;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Trigger\Action;
use ITRocks\Framework\Trigger\Change;
use ITRocks\Framework\User;
use ITRocks\Framework\View;

/**
 * Change trigger plugin
 *
 * @feature Trigger actions on data changes
 */
class Plugin implements Registerable
{
	use Has_Get;

	//------------------------------------------------------------------------------ $no_change_cache
	/**
	 * Class names that have no change triggers
	 *
	 * @var array Change[][]
	 */
	protected $no_change_cache = [];

	//----------------------------------------------------------------------------------- $run_action
	/**
	 * @var boolean
	 */
	protected $run_action = false;

	//------------------------------------------------------------------------------------ afterWrite
	/**
	 * @param $object object
	 * @param $after_write_annotation string @values after_create, after_update, after_write
	 */
	public function afterWrite($object, $after_write_annotation)
	{
		if (!($identifier = Dao::getObjectIdentifier($object))) {
			return;
		}
		foreach ($this->changeTriggers($object) as $change) {
			$run = Dao::searchOne([
				'change'     => $change,
				'class_name' => $change->class_name,
				'identifier' => $identifier,
				'step'       => [Run::AFTER, Run::BEFORE, Run::PENDING]
			], Run::class);
			if (
				!$run
				&& (
					!$change->before_condition
					|| (
						($after_write_annotation === Write::AFTER_CREATE)
						&& $change->conditionIsNull($change->before_condition)
					)
				)
			) {
				$run = new Run();
				$run->change     = $change;
				$run->class_name = $change->class_name;
				$run->identifier = $identifier;
			}
			if ($run) {
				$run->step = Run::AFTER;
				Dao::write($run, Dao::getObjectIdentifier($run) ? Dao::only('step') : []);
				$action_link = View::link(Change::class, 'run');
				// launch next step as an action (will need a running server)
				$now  = Date_Time::now();
				$user = User::current();
				if (!Dao::searchOne(
					[
						'action'    => $action_link,
						'as_user'   => $user,
						'keep_user' => false,
						'next'      => Func::lessOrEqual($now),
						'status'    => Action\Status::PENDING
					],
					Action::class
				)) {
					$action          = new Action();
					$action->action  = $action_link;
					$action->as_user = $user;
					$action->next    = $now;
					Dao::write($action);
				}
			}
		}
	}

	//----------------------------------------------------------------------------------- beforeWrite
	/**
	 * @param $object object
	 */
	public function beforeWrite($object)
	{
		if (!($identifier = Dao::getObjectIdentifier($object))) {
			return;
		}
		foreach ($this->changeTriggers($object) as $change) {
			$run = Dao::searchOne([
				'change'     => $change,
				'class_name' => $change->class_name,
				'identifier' => $identifier,
				'step'       => [Run::AFTER, Run::BEFORE, Run::PENDING]
			], Run::class);
			if (!$run && $change->verifyConditions($object, $change->before_condition)) {
				$run = new Run();
				$run->change     = $change;
				$run->class_name = $change->class_name;
				$run->identifier = $identifier;
				$run->step       = Run::BEFORE;
				Dao::write($run);
			}
		}
	}

	//-------------------------------------------------------------------------------- changeTriggers
	/**
	 * @param $object object
	 * @return Change[]
	 */
	protected function changeTriggers($object)
	{
		$class_name = get_class($object);
		if (!isset($this->no_change_cache[$class_name])) {
			do {
				$class_names[$class_name] = $class_name;
			}
			while ($class_name = get_parent_class($class_name));
			$change_triggers = Dao::search(['class_name' => array_values($class_names)], Change::class);
			$this->no_change_cache[$class_name] = $change_triggers;
		}
		return $this->no_change_cache[$class_name];
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod ([Write::class, 'afterWrite'],  [$this, 'afterWrite']);
		$aop->beforeMethod([Write::class, 'beforeWrite'], [$this, 'beforeWrite']);
	}

	//------------------------------------------------------------------------------------ resetCache
	/**
	 * @param $class_name string
	 */
	public function resetCache($class_name)
	{
		do {
			if (isset($this->no_change_cache[$class_name])) {
				unset($this->no_change_cache[$class_name]);
			}
		}
		while ($class_name = get_parent_class($class_name));
	}

}
