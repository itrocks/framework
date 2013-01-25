<?php
namespace SAF\Framework;

abstract class List_Controller extends Output_Controller
{

	//--------------------------------------------------------------------------- getSelectionButtons
	/**
	 * @param $class_name string
	 * @return Button[]
	 */
	protected function getSelectionButtons($class_name)
	{
		return array();
	}

}
