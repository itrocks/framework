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
	 * @noinspection PhpDocSignatureInspection $settings
	 * @param $object     object|string object or class name
	 * @param $parameters array parameters
	 * @param $settings   Output_Setting\Set&Setting\Custom\Set|null
	 * @return Button[]
	 */
	public function getGeneralButtons(
		object|string $object, array $parameters, Setting\Custom\Set $settings = null
	) : array
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
