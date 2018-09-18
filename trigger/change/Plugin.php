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

	//------------------------------------------------------------------------ $no_after_change_cache
	/**
	 * Class names that have no 'after condition'change triggers
	 *
	 * @var string[]
	 */
	protected $no_after_change_cache = [];

	//----------------------------------------------------------------------- $no_before_change_cache
	/**
	 * Class names that have no 'before condition' change triggers
	 *
	 * @var string[]
	 */
	protected $no_before_change_cache = [];

	//------------------------------------------------------------------------------------ afterWrite
	/**
	 * @param $object object
	 */
	public function afterWrite($object)
	{
		if (!($identifier = Dao::getObjectIdentifier($object))) {
			return;
		}
		$class_name = Builder::current()->sourceClassName(get_class($object));
		if (isset($this->no_after_change_cache[$class_name])) {
			return;
		}
		$change_triggers = Dao::search(['class_name' => $class_name], Change::class);
		if (!$change_triggers) {
			$this->no_after_change_cache[$class_name] = true;
			return;
		}
		foreach ($change_triggers as $change) {
			$run = Dao::searchOne([
				'change'     => $change,
				'class_name' => $class_name,
				'identifier' => $identifier,
				'step'       => Run::BEFORE
			], Run::class);
			if (!$run) {
				$run             = new Run();
				$run->change     = $change;
				$run->class_name = $class_name;
				$run->identifier = $identifier;
			}
			$run->step = Run::AFTER;
			Dao::write($run, Dao::only('step'));
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
		$class_name = Builder::current()->sourceClassName(get_class($object));
		if (isset($this->no_before_change_cache[$class_name])) {
			return;
		}
		$change_triggers = Dao::search(['class_name' => $class_name], Change::class);
		if (!$change_triggers) {
			$this->no_before_change_cache[$class_name] = true;
			return;
		}
		unset($this->no_after_change_cache[$class_name]);
		foreach ($change_triggers as $change) {
			$run = Dao::searchOne([
				'change'     => $change,
				'class_name' => $class_name,
				'identifier' => $identifier,
				'step'       => Run::BEFORE
			], Run::class);
			$change->before_condition;
			if (!$run && $change->verifyConditions($object, $change->before_condition)) {
				$run = new Run();
				$run->change     = $change;
				$run->class_name = $class_name;
				$run->identifier = $identifier;
				$run->step       = Run::BEFORE;
				Dao::write($run);
			}
		}
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
