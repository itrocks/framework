<?php
namespace SAF\Framework;

class Default_Output_Controller implements Output_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	public function getGeneralButtons($object)
	{
		return array(
			new Button(
				"Write", View::link($object, "write"), "write", array(".ifedit", ".submit", "#messages")
			),
			new Button(
				"Duplicate", View::link($object, "duplicate"), "duplicate"
			)
		);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default "form-typed" output view controller
	 *
	 * @param Controller_Parameters $parameters
	 * @param array  $form
	 * @param array  $files
	 * @param string $class_name
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = $parameters->getObjects();
		$object = reset($parameters);
		if (!$object || !is_object($object) || (get_class($object) !== $class_name)) {
			$object = new $class_name();
			$parameters = array_merge(array($class_name => $object), $parameters);
		}
		$parameters["general_buttons"]   = $this->getGeneralButtons($object);
		View::run($parameters, $form, $files, $class_name, "output");
	}

}
