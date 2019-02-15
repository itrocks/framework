<?php
namespace ITRocks\Framework\RAD\Feature;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Component\Button\No_General_Buttons;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Feature\List_;
use ITRocks\Framework\Feature\List_Setting;
use ITRocks\Framework\Setting;

/**
 * RAD feature list controller
 */
class List_Controller extends List_\Controller
{
	use No_General_Buttons;

	//--------------------------------------------------------------------------- getSelectionButtons
	/**
	 * @param $class_name    string class name
	 * @param $parameters    string[] parameters
	 * @param $list_settings Setting\Custom\Set|List_Setting\Set
	 * @return Button[]
	 */
	public function getSelectionButtons(
		/** @noinspection PhpUnusedParameterInspection @implements */
		$class_name, array $parameters, Setting\Custom\Set $list_settings = null
	) {
		$buttons = parent::getSelectionButtons($class_name, $parameters, $list_settings);
		return isset($buttons[Feature::F_EXPORT])
			? [Feature::F_EXPORT => $buttons[Feature::F_EXPORT]]
			: [];
	}

}
