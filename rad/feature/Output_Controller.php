<?php
namespace ITRocks\Framework\RAD\Feature;

use ITRocks\Framework\Controller;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\RAD\Feature;
use ITRocks\Framework\Setting\Custom_Settings;
use ITRocks\Framework\View;
use ITRocks\Framework\Widget\Button;
use ITRocks\Framework\Widget\Output;

/**
 * RAD feature output controller
 */
class Output_Controller extends Output\Output_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $object     Feature|string
	 * @param $parameters array
	 * @param $settings   Custom_Settings|null
	 * @return Button[]
	 */
	public function getGeneralButtons($object, array $parameters, Custom_Settings $settings = null)
	{
		$buttons = parent::getGeneralButtons($object, $parameters, $settings);
		$feature = $object;

		unset($buttons[Controller\Feature::F_EDIT]);

		if ($feature->status === Status::AVAILABLE) {
			$buttons[Install_Controller::FEATURE] = new Button(
				'Install',
				View::link($object, Install_Controller::FEATURE),
				Install_Controller::FEATURE,
				Target::MESSAGES
			);
		}

		if ($feature->status === Status::INSTALLED) {
			$buttons[Uninstall_Controller::FEATURE] = new Button(
				'Uninstall',
				View::link($object, Uninstall_Controller::FEATURE),
				Uninstall_Controller::FEATURE,
				Target::MESSAGES
			);
		}

		return $buttons;
	}

}
