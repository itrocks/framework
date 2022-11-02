<?php
namespace ITRocks\Framework\Feature\List_Setting;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Feature\List_;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tools;
use ITRocks\Framework\View;

/**
 * Default data list setting feature controller
 */
class Controller implements Default_Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'listSetting';

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files, string $class_name)
		: ?string
	{
		$element_class_name = Tools\Set::elementClassNameOf($class_name);
		$parameters         = $parameters->getObjects();
		$list_controller    = new List_\Controller();
		$list_settings      = Set::current($element_class_name);
		$did_change         = $list_controller->applyParametersToListSettings(
			$list_settings, $parameters, $form
		);
		// TODO Remove save() once we have a generic validator (parser) not depending of SQL that we could fire before save!
		if (!is_null($did_change)) {
			$list_settings->save($parameters['title'] ?? '');
		}

		if (isset($parameters['remove_property'])) {
			Main::$current->redirect(View::link($class_name), Target::MAIN);
		}

		if (!isset($parameters['title'])) {
			$parameters['title'] = Loc::tr('list setting');
		}

		return View::run($parameters, $form, $files, $class_name, static::FEATURE);
	}

}
