<?php
namespace ITrocks\Framework\Logger\Entry;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Feature\List_;
use ITRocks\Framework\Feature\List_Setting;
use ITRocks\Framework\Setting;

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
		return $buttons;
	}

}
