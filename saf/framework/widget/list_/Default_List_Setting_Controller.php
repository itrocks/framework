<?php
namespace SAF\Framework\Widget\List_;

use SAF\Framework\Controller\Default_Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Tools\Namespaces;
use SAF\Framework\Tools\Set;
use SAF\Framework\View;

/**
 * Default listSetting feature controller
 */
class Default_List_Setting_Controller implements Default_Feature_Controller
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
		$element_class_name = Namespaces::fullClassName(Set::elementClassNameOf($class_name));
		$parameters = $parameters->getObjects();
		$list_controller = new Default_List_Controller();
		$list_settings = List_Settings::current($element_class_name);
		$list_controller->applyParametersToListSettings($list_settings, $parameters, $form);
		return View::run($parameters, $form, $files, $class_name, 'listSetting');
	}

}
