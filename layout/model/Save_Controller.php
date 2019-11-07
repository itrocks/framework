<?php
namespace ITRocks\Framework\Layout\Model;

use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Feature\Save;

/**
 * Layout model save controller
 */
class Save_Controller extends Save\Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return string
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		unset($form['search']);
		return parent::run($parameters, $form, $files, $class_name);
	}

}
