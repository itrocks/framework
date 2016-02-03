<?php
namespace SAF\Framework\Widget\Button;

use SAF\Framework\Widget\Button;

/**
 * Apply this contract to all controllers that need a getSelectionButtons() method.
 */
interface Has_Selection_Buttons
{

	//--------------------------------------------------------------------------- getSelectionButtons
	/**
	 * @param $class_name string class name
	 * @param $parameters string[] parameters
	 * @return Button[]
	 */
	public function getSelectionButtons($class_name, $parameters);

}
