<?php
namespace ITrocks\Framework\Logger\Entry;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Setting\Custom_Settings;
use ITRocks\Framework\Widget\Button;
use ITRocks\Framework\Widget\Data_List;
use ITRocks\Framework\Widget\Data_List_Setting\Data_List_Settings;

/**
 * Logger entry data list controller
 */
class Data_List_Controller extends Data_List\Data_List_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $class_name string The context object or class name
	 * @param $parameters array Parameters prepared to the view. 'selection_buttons' to be added
	 * @param $settings   Custom_Settings|Data_List_Settings
	 * @return Button[]
	 */
	public function getGeneralButtons(
		$class_name, array $parameters, Custom_Settings $settings = null
	) {
		$buttons = parent::getGeneralButtons($class_name, $parameters, $settings);
		unset($buttons[Feature::F_ADD]);
		return $buttons;
	}

}
