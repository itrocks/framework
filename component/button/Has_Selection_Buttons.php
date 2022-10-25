<?php
namespace ITRocks\Framework\Component\Button;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Feature\List_Setting;

/**
 * Apply this contract to all controllers that need a getSelectionButtons() method.
 */
interface Has_Selection_Buttons
{

	//----------------------------------------------------------------------------- SELECTION_BUTTONS
	const SELECTION_BUTTONS = 'selection_buttons';

	//--------------------------------------------------------------------------- getSelectionButtons
	/**
	 * Gets the default selection buttons (selection buttons generated by the controller)
	 * This can be overridden to add default buttons to your controller
	 *
	 * @param $class_name string The context class name
	 * @param $parameters array Parameters prepared for the view
	 * @param $settings   List_Setting\Set|null The controller custom settings, if set
	 * @return Button[]
	 */
	public function getSelectionButtons(
		string $class_name, array $parameters, List_Setting\Set $settings = null
	) : array;

}
