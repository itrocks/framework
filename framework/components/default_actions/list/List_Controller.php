<?php
namespace SAF\Framework;

abstract class List_Controller extends Output_Controller
{

	//--------------------------------------------------------------------------- getSelectionButtons
	/**
	 * @param string $class_name
	 * @return Button[]
	 */
	protected function getSelectionButtons($class_name)
	{
		return array();
	}

}
