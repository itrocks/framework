<?php
namespace ITRocks\Framework\Controller;

use ITRocks\Framework\Controller;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\View;
use stdClass;

/**
 * The default controller launches a view corresponding to the original controller name
 *
 * It is called if no other specific or default controller is implemented
 */
class Default_Controller implements Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default 'view-typed' controller
	 *
	 * Loads data from objects given as parameters, then run the view associated to the first parameter class.
	 * This is called when no other controller was found for the first parameter object.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters   Parameters
	 * @param $form         array
	 * @param $files        array[]
	 * @param $class_name   string
	 * @param $feature_name string
	 * @return ?string
	 */
	public function run(
		Parameters $parameters, array $form, array $files, string $class_name, string $feature_name
	) : ?string
	{
		/** @noinspection PhpUnhandledExceptionInspection class_exists */
		$constructor = class_exists($class_name)
			? (new Reflection_Class($class_name))->getConstructor()
			: null;
		if (!$constructor || !$constructor->getMandatoryParameters()) {
			$parameters->getMainObject($class_name);
		}
		else {
			$parameters->getMainObject(stdClass::class);
		}
		$parameters = $parameters->getObjects();
		return View::run($parameters, $form, $files, $class_name, $feature_name);
	}

}
