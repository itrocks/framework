<?php
namespace ITrocks\Framework\Logger\Entry;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Setting\Custom_Settings;
use ITRocks\Framework\Tools\Color;
use ITRocks\Framework\View;
use ITRocks\Framework\Widget\Button;
use ITRocks\Framework\Widget\Data_List;
use ITRocks\Framework\Widget\Data_List_Setting\Data_List_Settings;

/**
 * Class List_Controller
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
		return [
			Feature::F_IMPORT => new Button(
				'Import',
				View::link($class_name, Feature::F_IMPORT),
				Feature::F_IMPORT,
				[Target::MAIN, new Color(Color::GREEN)]
			)
		];
	}

}
