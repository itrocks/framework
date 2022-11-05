<?php
namespace ITRocks\Framework\Trigger\Change;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link\Write;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Trigger;
use ITRocks\Framework\Trigger\Change;
use ITRocks\Framework\View;

/**
 * Change trigger plugin
 *
 * @feature Trigger actions on data changes
 */
class Plugin extends Trigger\Plugin
{

	//------------------------------------------------------------------------------ $no_change_cache
	/**
	 * Class names that have no change triggers
	 *
	 * @var array Change[][]
	 */
	protected array $no_change_cache = [];

	//------------------------------------------------------------------------------------ afterWrite
	/**
	 * @param $object object
	 * @param $after_write_annotation string @values after_create, after_update, after_write
	 */
	public function afterWrite(object $object, string $after_write_annotation) : void
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
				$run->step       = Run::AFTER;
				Dao::write($run);
			}
			if ($run && ($run->step !== Run::PENDING)) {
				if ($run->step !== Run::AFTER) {
					$run->step = Run::AFTER;
					Dao::write($run, Dao::only('step'));
				}
				$action_link = View::link(Change::class, 'run');
				$this->launchNextStep($action_link);
			}
		}
	}

	//----------------------------------------------------------------------------------- beforeWrite
	/**
	 * @param $object object
	 */
	public function beforeWrite(object $object) : void
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
	protected function changeTriggers(object $object) : array
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
	public function register(Register $register) : void
	{
		$aop = $register->aop;
		$aop->afterMethod ([Write::class, 'afterWrite'],  [$this, 'afterWrite']);
		$aop->beforeMethod([Write::class, 'beforeWrite'], [$this, 'beforeWrite']);
	}

	//------------------------------------------------------------------------------------ resetCache
	/**
	 * @param $class_name string
	 */
	public function resetCache(string $class_name) : void
	{
		do {
			if (isset($this->no_change_cache[$class_name])) {
				unset($this->no_change_cache[$class_name]);
			}
		}
		while ($class_name = get_parent_class($class_name));
	}

}
