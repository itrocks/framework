<?php
namespace ITRocks\Framework\Feature\Edit;

use ITRocks\Framework\Reflection\Attribute\Property\User;
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
	protected function filterProperties(object $object, array $properties) : array
	{
		return $properties;
	}

	//-------------------------------------------------------------------------------------- getField
	/**
	 * Return the current data as a field : editable in this case
	 *
	 * @param $template Template
	 * @param $name     string The name of the field
	 * @return string
	 */
	public function getField(Template $template, string $name = '') : string
	{
		return $this->getEdit($template, $name);
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
	protected function isPropertyVisible(Reflection_Property $property) : bool
	{
		return $property->isVisible(false)
			&& !User::of($property)->has(User::HIDE_EDIT)
			&& !User::of($property)->has(User::INVISIBLE_EDIT);
	}

}
