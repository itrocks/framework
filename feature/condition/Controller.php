<?php
namespace ITRocks\Framework\Feature\Condition;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Feature\Condition;
use ITRocks\Framework\Reflection\Annotation\Property\Mandatory_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\View;

/**
 * Condition controller
 *
 * Applies on Set
 */
class Controller implements Default_Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'condition';

	//------------------------------------------------------------------------ $mandatory_annotations
	/**
	 * @var Mandatory_Annotation[]
	 */
	protected $mandatory_annotations = [];

	//------------------------------------------------------------------------ $read_only_annotations
	/**
	 * @var User_Annotation[]
	 */
	protected $read_only_annotations = [];

	//---------------------------------------------------------------------------------- getCondition
	/**
	 * Load and return condition
	 *
	 * Default condition is a simple empty 'and' operator for multiple conditions
	 *
	 * @param $class_name string
	 * @return Condition
	 */
	protected function getCondition($class_name)
	{
		return new Condition($class_name, Func::andOp([]));
	}

	//----------------------------------------------------------------------------- prepareProperties
	/**
	 * Prepare properties to be fully editable for search criteria
	 *
	 * - remove user readonly annotation value
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 */
	protected function prepareProperties($class_name)
	{
		/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
		foreach ((new Reflection_Class($class_name))->getProperties([T_EXTENDS, T_USE]) as $property) {
			$mandatory = Mandatory_Annotation::of($property);
			if ($mandatory->value) {
				$mandatory->value              = false;
				$this->mandatory_annotations[] = $mandatory;
			}
			$user = User_Annotation::of($property);
			if ($user->has(User_Annotation::READONLY)) {
				$user->remove(User_Annotation::READONLY);
				$this->read_only_annotations[] = $user;
			}
		}
	}

	//------------------------------------------------------------------------------- resetProperties
	/**
	 * Reset properties as they were before working on condition view
	 *
	 * - get back @user readonly
	 */
	protected function resetProperties()
	{
		foreach ($this->mandatory_annotations as $mandatory) {
			$mandatory->value = true;
		}
		foreach ($this->read_only_annotations as $user) {
			$user->add(User_Annotation::READONLY);
		}
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Run method for a feature controller working for any class
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		$condition = $this->getCondition($class_name);
		$parameters->getMainObject($class_name);
		$parameters->set(self::FEATURE, $condition);
		$this->prepareProperties($class_name);
		$parameters = $parameters->getObjects();
		$output     = View::run($parameters, $form, $files, $class_name, self::FEATURE);
		$this->resetProperties();
		return $output;
	}

}
