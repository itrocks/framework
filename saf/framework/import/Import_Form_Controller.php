<?php
namespace SAF\Framework\Import;

use SAF\Framework\Controller\Default_Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Import;
use SAF\Framework\View;

/**
 * Default importForm feature controller
 */
class Import_Form_Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = $parameters->getObjects();
		array_unshift($parameters, new Import($class_name));
		$parameters['template'] = 'importForm';
		return View::run($parameters, $form, $files, $class_name, 'import');
	}

}
