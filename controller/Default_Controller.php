<?php
namespace SAF\Framework\Controller;

use SAF\Framework\Controller;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\View;
use StdClass;

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
	 * @param $parameters   Parameters
	 * @param $form         array
	 * @param $files        array
	 * @param $class_name   string
	 * @param $feature_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files, $class_name, $feature_name)
	{
		$constructor = (new Reflection_Class($class_name))->getConstructor();
		if (!$constructor || !$constructor->getMandatoryParameters()) {
			$parameters->getMainObject($class_name);
		}
		else {
			$parameters->getMainObject(StdClass::class);
		}
		$parameters = $parameters->getObjects();
		return View::run($parameters, $form, $files, $class_name, $feature_name);
	}

}
