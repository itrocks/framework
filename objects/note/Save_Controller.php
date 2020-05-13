<?php
namespace ITRocks\Framework\Objects\Note;

use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Save;

/**
 * Note save controller
 */
class Save_Controller extends Save\Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		if (isset($form['object']) && strpos($form['object'], ':')) {
			$form['object'] = Dao::read(rParse($form['object'], ':'), lParse($form['object'], ':'));
		}
		return parent::run($parameters, $form, $files, $class_name);
	}

}
