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
	 * @param Controller_Parameters $parameters
	 * @param array  $form
	 * @param array  $files
	 * @param string $class_name
	 * @param string $feature_name
	 */
	public function run($parameters, $form, $files, $class_name, $feature_name)
	{
		$parameters = $parameters->getObjects();
		$object = reset($parameters);
		if (!$object || !is_object($object) || (get_class($object) !== $class_name)) {
			$object = new $class_name();
			$parameters = array_merge(array($class_name => $object), $parameters);
		}
		View::run($parameters, $form, $files, $class_name, $feature_name);
	}

}
