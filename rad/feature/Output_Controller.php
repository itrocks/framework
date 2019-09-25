<?php
namespace ITRocks\Framework\RAD\Feature;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Controller;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Feature\Output;
use ITRocks\Framework\RAD\Feature;
use ITRocks\Framework\Setting;
use ITRocks\Framework\View;

/**
 * RAD feature output controller
 */
class Output_Controller extends Output\Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $object     Feature|string
	 * @param $parameters array
	 * @param $settings   Setting\Custom\Set|null
	 * @return Button[]
	 */
	public function getGeneralButtons($object, array $parameters, Setting\Custom\Set $settings = null)
	{
		$buttons[Controller\Feature::F_CLOSE]
			= parent::getGeneralButtons($object, $parameters, $settings)[Controller\Feature::F_CLOSE];

		$feature = $object;
		if ($feature->status === Status::AVAILABLE) {
			$buttons[Install_Controller::FEATURE] = new Button(
				'Install',
				View::link($object, Install_Controller::FEATURE),
				Install_Controller::FEATURE,
				Target::RESPONSES
			);
		}

		if ($feature->status === Status::INSTALLED) {
			$buttons[Uninstall_Controller::FEATURE] = new Button(
				'Uninstall',
				View::link($object, Uninstall_Controller::FEATURE),
				Uninstall_Controller::FEATURE,
				Target::RESPONSES
			);
		}

		return $buttons;
	}

}
