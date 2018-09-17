<?php
namespace ITRocks\Framework\Trigger\Change;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link\Write;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Trigger\Change;

/**
 * Change trigger plugin
 */
class Plugin implements Registerable
{

	//------------------------------------------------------------------------------------ afterWrite
	/**
	 * @param $object                 object
	 * @param $after_write_annotation string
	 */
	public function afterWrite($object, $after_write_annotation)
	{
		if (
			($after_write_annotation !== Write::AFTER_WRITE)
			|| !($identifier = Dao::getObjectIdentifier($object))
		) {
			return;
		}
		$class_name      = Builder::current()->sourceClassName(get_class($object));
		$change_triggers = Dao::search(
			['after_condition' => Func::isNotNull(), 'class_name' => $class_name], Change::class
		);
		foreach ($change_triggers as $change) {
			$run = Dao::searchOne([
				'change'     => $change,
				'class_name' => $class_name,
				'identifier' => $identifier,
				'step'       => Run::BEFORE
			], Run::BEFORE);
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
	 * @param $object                  object
	 * @param $before_write_annotation string
	 */
	public function beforeWrite($object, $before_write_annotation)
	{
		if (
			($before_write_annotation !== Write::BEFORE_WRITE)
			|| !($identifier = Dao::getObjectIdentifier($object))
		) {
			return;
		}
		$class_name      = Builder::current()->sourceClassName(get_class($object));
		$change_triggers = Dao::search(
			['before_condition' => Func::isNotNull(), 'class_name' => $class_name], Change::class
		);
		foreach ($change_triggers as $change) {
			$search_run = [
				'change'     => $change,
				'class_name' => $class_name,
				'identifier' => $identifier,
				'step'       => Run::BEFORE
			];
			if (
				!Dao::searchOne($search_run, Run::class)
				&& $change->verifyConditions($object, $change->before_condition)
			) {
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
