<?php
namespace ITRocks\Framework\Trigger\Feature;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Uri;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Trigger;
use ITRocks\Framework\Trigger\Feature;
use ITRocks\Framework\View;

/**
 * Feature trigger plugin
 *
 * @feature Trigger actions on feature calls
 */
class Plugin extends Trigger\Plugin
{

	//----------------------------------------------------------------------------- $no_feature_cache
	/**
	 * Class names that have no feature triggers
	 *
	 * @var array Feature[][]
	 */
	protected $no_feature_cache = [];

	//------------------------------------------------------------------------ afterExecuteController
	/**
	 * @param $uri Uri
	 */
	public function afterExecuteController(Uri $uri)
	{
		$object = $uri->parameters->getMainObject();
		if (!($identifier = Dao::getObjectIdentifier($object))) {
			return;
		}
		foreach ($this->featureTriggers($object) as $feature) {
			$run = Dao::searchOne([
				'feature'    => $feature,
				'class_name' => $feature->class_name,
				'identifier' => $identifier,
				'step'       => Run::BEFORE
			], Run::class);
			if (!$run && !$feature->before_condition) {
				$run = new Run();
				$run->feature    = $feature;
				$run->class_name = $feature->class_name;
				$run->identifier = $identifier;
			}
			if ($run) {
				$run->step = Run::AFTER;
				Dao::write($run, Dao::only('step'));
				$action_link = View::link(Feature::class, 'run');
				$this->launchNextStep($action_link);
			}
		}
	}

	//----------------------------------------------------------------------- beforeExecuteController
	/**
	 * @param $uri Uri
	 */
	public function beforeExecuteController(Uri $uri)
	{
		$parameters = clone $uri->parameters;
		$object     = $parameters->getMainObject();
		if (!($identifier = Dao::getObjectIdentifier($object))) {
			return;
		}
		foreach ($this->featureTriggers($object) as $feature) {
			$run = Dao::searchOne([
				'feature'    => $feature,
				'class_name' => $feature->class_name,
				'identifier' => $identifier,
				'step'       => [Run::AFTER, Run::BEFORE, Run::PENDING]
			], Run::class);
			if (!$run && $feature->verifyConditions($object, $feature->before_condition)) {
				$run = new Run();
				$run->feature    = $feature;
				$run->class_name = $feature->class_name;
				$run->identifier = $identifier;
				$run->step       = Run::BEFORE;
				Dao::write($run);
			}
		}
	}

	//------------------------------------------------------------------------------- featureTriggers
	/**
	 * @param $object object
	 * @return Feature[]
	 */
	protected function featureTriggers($object)
	{
		$class_name = Builder::current()->sourceClassName(get_class($object));
		if (!isset($this->no_feature_cache[$class_name])) {
			$feature_triggers = Dao::search(['class_name' => $class_name], Feature::class);
			$this->no_feature_cache[$class_name] = $feature_triggers;
		}
		return $this->no_feature_cache[$class_name];
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod ([Main::class, 'executeController'], [$this, 'afterExecuteController']);
		$aop->beforeMethod([Main::class, 'executeController'], [$this, 'beforeExecuteController']);
	}

}
