<?php
namespace ITRocks\Framework\Component\Button;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Setting;

/**
 * @implements Has_Selection_Buttons
 */
trait No_Selection_Buttons
{

	//--------------------------------------------------------------------------- getSelectionButtons
	/**
	 * @param $class_name    string class name
	 * @param $parameters    string[] parameters
	 * @param $list_settings Setting\Custom\Set|null
	 * @return Button[]
	 */
	public function getSelectionButtons(
		/** @noinspection PhpUnusedParameterInspection @implements */
		string $class_name, array $parameters, Setting\Custom\Set $list_settings = null
	) : array
	{
		return [];
	}

}
