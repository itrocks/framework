<?php
namespace ITRocks\Framework\Layout\Model;

use Exception;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Widget\Write;

/**
 * Layout model write controller
 */
class Write_Controller extends Write\Write_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return string
	 * @throws Exception
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		unset($form['pages']);
		unset($form['search']);
		return parent::run($parameters, $form, $files, $class_name);
	}

}
