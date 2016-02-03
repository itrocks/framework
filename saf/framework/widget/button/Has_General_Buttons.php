<?php
namespace SAF\Framework\Widget\Button;

use SAF\Framework\Widget\Button;

/**
 * Apply this contract to all controllers that need a getGeneralButtons() method.
 */
interface Has_General_Buttons
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $object object|string The context object or class name
	 * @param $parameters array     Parameters prepared for the view
	 * @return Button[]
	 */
	public function getGeneralButtons($object, $parameters);

}
