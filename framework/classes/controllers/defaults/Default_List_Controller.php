<?php
namespace SAF\Framework;

class Default_List_Controller implements Class_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default "list-typed" controller
	 *
	 * @param Controller_Parameters $parameters
	 * @param array  $form
	 * @param array  $files
	 * @param string $class_name
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$element_class_name = Set::elementClassNameOf($class_name);
		$objects = Dao::readAll($element_class_name);
		$parameters = array_merge(array($class_name => $objects), $parameters);
		View::run($parameters, $form, $files, $class_name, "list");
	}

}
