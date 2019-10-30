<?php
namespace ITRocks\Framework\Layout\Print_Model;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Default_View;

/**
 * Write controller : when added, redirect to edit instead of output
 */
class Write_Html_View extends Default_View
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters   array
	 * @param $form         array
	 * @param $files        array[]
	 * @param $class_name   string
	 * @param $feature_name string
	 * @return mixed|void
	 */
	public function run(array $parameters, array $form, array $files, $class_name, $feature_name)
	{
		$parameters[Parameter::THEN] = View::link(reset($parameters), Feature::F_EDIT);
		return parent::run($parameters, $form, $files, $class_name, $feature_name);
	}

}
