<?php
namespace SAF\Framework\Widget\Edit;

use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\View\Html\Template\Functions;

/**
 * Edit widget html template functions specifics
 */
class Html_Template_Functions extends Functions
{

	//----------------------------------------------------------------------------- isPropertyVisible
	/**
	 * This is the same as its parent, but without the @user hide_empty test :
	 * On forms, even empty values should be edited. @user hide_empty is for output only
	 * Special case : the property is @user readonly. In this case it's still hidden if empty
	 *
	 * @param $property Reflection_Property
	 * @return boolean
	 */
	protected function isPropertyVisible(Reflection_Property $property)
	{
		return $property->isVisible(false);
	}

}
