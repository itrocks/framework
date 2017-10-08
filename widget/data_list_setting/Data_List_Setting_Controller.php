<?php
namespace ITRocks\Framework\Widget\Data_List_Setting;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Tools\Set;
use ITRocks\Framework\View;
use ITRocks\Framework\Widget\Data_List\Data_List_Controller;

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
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		$element_class_name = Set::elementClassNameOf($class_name);
		$parameters         = $parameters->getObjects();
		$list_controller    = new Data_List_Controller();
		$data_list_settings = Data_List_Settings::current($element_class_name);
		$did_change         = $list_controller->applyParametersToListSettings(
			$data_list_settings, $parameters, $form
		);
		// TODO Remove save() once we have a generic validator (parser) not depending of SQL that we could fire before save!
		if (!is_null($did_change)) {
			$data_list_settings->save();
		}
		return View::run($parameters, $form, $files, $class_name, 'dataListSetting');
	}

}
