<?php
namespace SAF\Framework;

/**
 * Default listSetting feature controller
 */
class Default_List_Setting_Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$element_class_name = Namespaces::fullClassName(Set::elementClassNameOf($class_name));
		$parameters = $parameters->getObjects();
		$list_controller = new Default_List_Controller();
		$list_settings = $list_controller->getListSettings($element_class_name);
		$list_controller->applyParametersToListSettings($list_settings, $parameters, $form);
		return View::run($parameters, $form, $files, $class_name, "listSetting");
	}

}
