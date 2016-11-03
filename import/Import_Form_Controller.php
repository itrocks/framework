<?php
namespace ITRocks\Framework\Import;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Import;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;

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
		$parameters[Template::TEMPLATE] = 'importForm';
		return View::run($parameters, $form, $files, $class_name, Feature::F_IMPORT);
	}

}
