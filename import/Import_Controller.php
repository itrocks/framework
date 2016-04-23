<?php
namespace SAF\Framework\Import;

use SAF\Framework\Controller\Default_Feature_Controller;
use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Main;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\View;

/**
 * Default import controller
 */
class Import_Controller implements Default_Feature_Controller
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
		$sub_feature = $parameters->shiftUnnamed();
		if (!$sub_feature) {
			$sub_feature = 'form';
		}
		$get = $parameters->toGet();
		return (new Main())->runController(
			View::link($class_name, Feature::F_IMPORT), $get, $form, $files,
			'import' . ucfirst($sub_feature)
		);
	}

}
