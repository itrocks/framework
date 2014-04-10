<?php
namespace SAF\Framework\Import;

use SAF\Framework\Controller\Default_Feature_Controller;
use SAF\Framework\Controller\Main;
use SAF\Framework\Controller\Parameters;

/**
 * Default import controller
 */
class Default_Import_Controller implements Default_Feature_Controller
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
		$feature = 'import' . ucfirst($sub_feature);
		return (new Main())->runController(
			SL . $class_name . SL . $feature, $get, $form, $files
		);
	}

}
