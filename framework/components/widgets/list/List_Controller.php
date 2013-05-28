<?php
namespace SAF\Framework;

/**
 * All list controllers should herited List_Controller, that gives a default getSelectionButtons() implementation
 */
abstract class List_Controller extends Output_Controller
{

	//--------------------------------------------------------------------------- getSelectionButtons
	/**
	 * @param $class_name string
	 * @return Button[]
	 */
	protected function getSelectionButtons(
		/** @noinspection PhpUnusedParameterInspection needed for plugins or overriding */
		$class_name
	) {
		return array();
	}

}
