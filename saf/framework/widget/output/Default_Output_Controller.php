<?php
namespace SAF\Framework\Widget\Output;

use SAF\Framework\Print_Model;
use SAF\Framework\Tools\Color;
use SAF\Framework\Tools\Names;
use SAF\Framework\Tools\Namespaces;
use SAF\Framework\View;
use SAF\Framework\Widget\Button;

/**
 * The default output controller will be called if no output controller is available for a class
 */
class Default_Output_Controller extends Output_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $object object|string object or class name
	 * @param $parameters string[] parameters
	 * @return Button[]
	 */
	protected function getGeneralButtons($object, $parameters)
	{
		return [
			new Button('Close', View::link(Names::classToSet(get_class($object))), 'close',
				[Color::of('close'), '#main']
			),
			new Button('Edit', View::link($object, 'edit'), 'edit',
				[Color::of('green'), '#main']
			),
			new Button('Print', View::link($object, 'print'), 'print',
				[Color::of('blue'), '#main', 'sub_buttons' => [
					new Button(
						'Models',
						View::link(
							Names::classToSet(Print_Model::class), 'list',
							Namespaces::shortClassName(get_class($object))
						),
						'models',
						'#main'
					)
				]]
			)
		];
	}

}
