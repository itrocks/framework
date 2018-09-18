<?php
namespace ITRocks\Framework\Trigger\Change;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link\Write;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Trigger\Change;

/**
 * Change trigger plugin
 *
 * @feature Trigger actions on data changes
 */
class Plugin implements Registerable
{

	//------------------------------------------------------------------------------ $no_change_cache
	/**
	 * Class names that have no change triggers
	 *
	 * @var string[]
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
	 */
	public function afterWrite($object)
	{
		if (!($identifier = Dao::getObjectIdentifier($object))) {
			return;
		}
		foreach ($this->changeTriggers($object) as $change) {
			$run = Dao::searchOne([
				'change'     => $change,
				'class_name' => $change->class_name,
				'identifier' => $identifier,
				'step'       => Run::BEFORE
			], Run::class);
			if ($run) {
				$run->step = Run::AFTER;
				Dao::write($run, Dao::only('step'));
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
		$class_name = Builder::current()->sourceClassName(get_class($object));
		if (!isset($this->no_change_cache[$class_name])) {
			$change_triggers = Dao::search(['class_name' => $class_name], Change::class);
			return $this->no_change_cache[$class_name] = $change_triggers;
		}
		return $this->no_change_cache[$class_name];
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod ([Write::class, 'afterWrite'],  [$this, 'afterWrite']);
		$aop->beforeMethod([Write::class, 'beforeWrite'], [$this, 'beforeWrite']);
	}

}
