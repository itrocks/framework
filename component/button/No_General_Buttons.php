<?php
namespace ITRocks\Framework\Component\Button;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Reflection\Attribute\Class_\Implement;
use ITRocks\Framework\Setting;

#[Implement(Has_General_Buttons::class)]
trait No_General_Buttons
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $class_name object|string The context object or class name
	 * @param $parameters array Parameters prepared to the view. 'selection_buttons' to be added
	 * @param $settings   Setting\Custom\Set|null
	 * @return Button[]
	 */
	public function getGeneralButtons(
		object|string $class_name, array $parameters, Setting\Custom\Set $settings = null
	) : array
	{
		return [];
	}

}
