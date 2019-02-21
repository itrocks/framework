<?php
namespace ITRocks\Framework\Feature\Edit;

use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\View\Html\Template;
use ITRocks\Framework\View\Html\Template\Functions;

/**
 * Edit widget html template functions specifics
 */
class Html_Template_Functions extends Functions
{

	//------------------------------------------------------------------------------ filterProperties
	/**
	 * Filter properties which @conditions values do not apply
	 *
	 * @param $object     object
	 * @param $properties Reflection_Property[]|string[] filter the list of properties
	 * @return string[]   filtered $properties
	 */
	protected function filterProperties($object, array $properties)
	{
		return $properties;
	}

	//-------------------------------------------------------------------------------------- getField
	/**
	 * Return the current data as a field : editable in this case
	 *
	 * @param $template Template
	 * @return mixed
	 */
	public function getField(Template $template)
	{
		return $this->getEdit($template);
	}

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
		return $property->isVisible(false)
			&& !User_Annotation::of($property)->has(User_Annotation::HIDE_EDIT)
			&& !User_Annotation::of($property)->has(User_Annotation::INVISIBLE_EDIT);
	}

}
