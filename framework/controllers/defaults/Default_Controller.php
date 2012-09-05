<?php
namespace SAF\Framework;

class Default_Controller implements Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param Controller_Parameters $parameters
	 * @param array  $form
	 * @param array  $files
	 * @param string $class_name
	 * @param string $feature_name
	 */
	public function run($parameters, $form, $files, $class_name, $feature_name)
	{
		$parameters = $parameters->getObjects();
		View::run($parameters, $form, $files, $class_name, $feature_name);
	}

}
