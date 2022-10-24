<?php
namespace ITRocks\Framework\Feature\Import;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Feature\Import;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;

/**
 * Default importForm feature controller
 */
class Import_Form_Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------- NEXT_STEP
	/**
	 * next step parameter : can be preview, execute, or a custom value
	 */
	const NEXT_STEP = 'next_step';

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files, string $class_name)
		: ?string
	{
		$parameters = $parameters->getObjects();
		array_unshift($parameters, new Import($class_name));
		$parameters[Template::TEMPLATE] = 'importForm';
		if (!isset($parameters[static::NEXT_STEP])) {
			$parameters[static::NEXT_STEP] = 'preview';
		}
		if (!isset($parameters[View::TARGET])) {
			$parameters[View::TARGET] = Target::MAIN;
		}
		return View::run($parameters, $form, $files, $class_name, Feature::F_IMPORT);
	}

}
