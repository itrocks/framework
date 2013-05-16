<?php
namespace SAF\Framework;

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
			)
		));
	}

}
