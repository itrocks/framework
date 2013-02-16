<?php
namespace SAF\Framework;

class Default_Output_Controller extends Output_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $object object|string object or class name
	 * @return Button[]
	 */
	protected function getGeneralButtons($object)
	{
		return Button::newCollection(array(
			array("Edit", View::link($object, "edit"), "edit", array(Color::of("green"), "#main"))
		));
	}

}
