<?php
namespace SAF\Framework;

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
		return Button::newCollection(array(
			array("Close",
				View::link(Names::classToSet(get_class($object))),
				"close",
				array(Color::of("close"), "#main")
			),
			array("Edit",
				View::link($object, "edit"),
				"edit",
				array(Color::of("green"), "#main")
			),
			array("Print",
				View::link($object, "print"),
				"print",
				array(Color::of("blue"), "#main", "sub_buttons" => array(
					array(
						"Models",
						View::link('SAF\Framework\Print_Model', "output", get_class($object)),
						"models",
						array("#main")
					)
				))
			)
		));
	}

}
