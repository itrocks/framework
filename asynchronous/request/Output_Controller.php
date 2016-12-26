<?php

namespace ITRocks\Framework\Asynchronous\Request;

use ITRocks\Framework\Setting\Custom_Settings;
use ITRocks\Framework\View;
use ITRocks\Framework\Widget\Button;

/**
 * Change buttons for output
 */
class Output_Controller extends \ITRocks\Framework\Widget\Output\Output_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $object     object|string
	 * @param $parameters array
	 * @param $settings   Custom_Settings|null
	 * @return \ITRocks\Framework\Widget\Button[]
	 */
	public function getGeneralButtons($object, array $parameters, Custom_Settings $settings = null)
	{
		$buttons = parent::getGeneralButtons($object, $parameters, $settings);
		$buttons['close'] = new Button('Close', View::link(get_class($object), 'list'));
		unset($buttons['print']);
		return $buttons;
	}

}
