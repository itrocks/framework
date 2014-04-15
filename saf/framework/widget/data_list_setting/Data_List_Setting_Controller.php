<?php
namespace SAF\Framework\Widget\Data_List_Setting;

use SAF\Framework\Controller\Default_Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Tools\Set;
use SAF\Framework\View;
use SAF\Framework\Widget\Data_List\Data_List_Controller;

/**
 * Default data list setting feature controller
 */
class Data_List_Setting_Controller implements Default_Feature_Controller
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
		$element_class_name = Set::elementClassNameOf($class_name);
		$parameters = $parameters->getObjects();
		$list_controller = new Data_List_Controller();
		$data_list_settings = Data_List_Settings::current($element_class_name);
		$list_controller->applyParametersToListSettings($data_list_settings, $parameters, $form);
		return View::run($parameters, $form, $files, $class_name, 'dataListSetting');
	}

}
