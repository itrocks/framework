<?php
namespace ITrocks\Framework\Logger\Entry;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Feature\List_;
use ITRocks\Framework\Feature\List_Setting;
use ITRocks\Framework\Setting;
use ITRocks\Framework\View;

/**
 * Logger entry data list controller
 */
class List_Controller extends List_\Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $class_name string The context object or class name
	 * @param $parameters array Parameters prepared to the view. 'selection_buttons' to be added
	 * @param $settings   Setting\Custom\Set|List_Setting\Set
	 * @return Button[]
	 */
	public function getGeneralButtons(
		$class_name, array $parameters, Setting\Custom\Set $settings = null
	) {
		$buttons = parent::getGeneralButtons($class_name, $parameters, $settings);
		unset($buttons[Feature::F_ADD]);
		unset($buttons[Feature::F_IMPORT]);
		return $buttons;
	}

	//--------------------------------------------------------------------------- getSelectionButtons
	/**
	 * @param $class_name    string class name
	 * @param $parameters    string[] parameters
	 * @param $list_settings Setting\Custom\Set|List_Setting\Set
	 * @return Button[]
	 */
	public function getSelectionButtons(
		$class_name, array $parameters, Setting\Custom\Set $list_settings = null
	) {
		$buttons = parent::getSelectionButtons($class_name, $parameters, $list_settings);
		unset($buttons[Feature::F_DELETE]);
		unset($buttons[Feature::F_PRINT]);
		$buttons[File_Export\Controller::FEATURE] = new Button(
			'File export',
			View::link($class_name, File_Export\Controller::FEATURE),
			File_Export\Controller::FEATURE,
			[View::TARGET => Target::TOP]
		);
		return $buttons;
	}

}
