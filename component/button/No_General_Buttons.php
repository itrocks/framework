<?php
namespace ITRocks\Framework\Component\Button;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Setting;

/**
 * @implements Has_General_Buttons
 */
trait No_General_Buttons
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $class_name string The context object or class name
	 * @param $parameters array Parameters prepared to the view. 'selection_buttons' to be added
	 * @param $settings   Setting\Custom\Set
	 * @return Button[]
	 */
	public function getGeneralButtons(
		/** @noinspection PhpUnusedParameterInspection @implements */
		$class_name, array $parameters, Setting\Custom\Set $settings = null
	) {
		return [];
	}

}
