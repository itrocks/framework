<?php
namespace ITRocks\Framework\Feature\Condition;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Feature\Condition;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Reflection\Attribute\Property\User;
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
	/** @var Mandatory[] */
	protected array $mandatory_annotations = [];

	//------------------------------------------------------------------------ $read_only_annotations
	/** @var User[] */
	protected array $read_only_annotations = [];

	//---------------------------------------------------------------------------------- getCondition
	/**
	 * Load and return condition
	 * Default condition is a simple empty 'and' operator for multiple conditions
	 */
	protected function getCondition(string $class_name) : Condition
	{
		return new Condition($class_name, Func::andOp([]));
	}

	//----------------------------------------------------------------------------- prepareProperties
	/**
	 * Prepare properties to be fully editable for search criteria
	 * - remove user readonly annotation value
	 */
	protected function prepareProperties(string $class_name) : void
	{
		/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
		foreach ((new Reflection_Class($class_name))->getProperties([T_EXTENDS, T_USE]) as $property) {
			$mandatory = Mandatory::of($property);
			if ($mandatory->value) {
				$mandatory->value              = false;
				$this->mandatory_annotations[] = $mandatory;
			}
			$user = User::of($property);
			if ($user->has(User::READONLY)) {
				$user->remove(User::READONLY);
				$this->read_only_annotations[] = $user;
			}
		}
	}

	//------------------------------------------------------------------------------- resetProperties
	/**
	 * Reset properties as they were before working on condition view
	 * - get back #User::READONLY
	 */
	protected function resetProperties() : void
	{
		foreach ($this->mandatory_annotations as $mandatory) {
			$mandatory->value = true;
		}
		foreach ($this->read_only_annotations as $user) {
			$user->add(User::READONLY);
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
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files, string $class_name)
		: ?string
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
