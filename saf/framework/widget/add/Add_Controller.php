<?php
namespace SAF\Framework\Widget\Add;

use SAF\Framework\Tools\Color;
use SAF\Framework\Tools\Names;
use SAF\Framework\View;
use SAF\Framework\Widget\Button;
use SAF\Framework\Widget\Edit\Edit_Controller;

/**
 * The default new controller is the same as an edit controller, that accepts no object
 */
class Add_Controller extends Edit_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $object     object|string object or class name
	 * @param $parameters string[] parameters
	 * @return Button[]
	 */
	protected function getGeneralButtons($object, $parameters)
	{
		$buttons = parent::getGeneralButtons($object, $parameters);
		return array_merge($buttons, [
			'close' => new Button('Close', View::link(Names::classToSet(get_class($object))), 'close',
				[new Color('close'), '#main']
			),
		]);
	}

}
