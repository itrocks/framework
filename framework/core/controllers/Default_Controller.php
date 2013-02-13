<?php
namespace SAF\Framework;

class Default_Controller implements Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default "view-typed" controller
	 *
	 * Loads data from objects given as parameters, then run the view associated to the first parameter class.
	 * This is called when no other controller was found for the first parameter object.
	 *
	 * @param $parameters Controller_Parameters
	 * @param $form array
	 * @param $files array
	 * @param $class_name string
	 * @param $feature_name string
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name, $feature_name)
	{
		$parameters = $parameters->getObjects();
		$object = reset($parameters);
		if (!$object || !is_object($object) || (get_class($object) !== $class_name)) {
			$object = class_exists($class_name)
				? new $class_name()
				: Set::instantiate($class_name);
			$parameters = array_merge(array($class_name => $object), $parameters);
		}
		return View::run($parameters, $form, $files, $class_name, $feature_name);
	}

}
