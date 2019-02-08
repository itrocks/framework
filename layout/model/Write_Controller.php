<?php
namespace ITRocks\Framework\Layout\Model;

use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Feature\Write;

/**
 * Layout model write controller
 */
class Write_Controller extends Write\Controller
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
