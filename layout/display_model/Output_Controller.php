<?php
namespace ITRocks\Framework\Layout\Display_Model;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Feature\Output\Controller;
use ITRocks\Framework\Feature\Output_Setting;
use ITRocks\Framework\Setting;
use ITRocks\Framework\View;

/**
 * Display model output controller
 */
class Output_Controller extends Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $object     object|string object or class name
	 * @param $parameters array parameters
	 * @param $settings   Setting\Custom\Set|Output_Setting\Set
	 * @return Button[]
	 */
	public function getGeneralButtons($object, array $parameters, Setting\Custom\Set $settings = null)
	{
		$buttons = parent::getGeneralButtons($object, $parameters, $settings);
		$buttons[Generate_Controller::FEATURE] = new Button(
			'Generate',
			View::link($object, Generate_Controller::FEATURE),
			Generate_Controller::FEATURE,
			Target::RESPONSES
		);
		return $buttons;
	}

}
