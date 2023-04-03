<?php
namespace ITRocks\Framework\Feature\List_;

use ITRocks\Framework\Feature\Edit;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Reflection\Attribute\Property\Multiline;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * HTML template functions for data list
 */
class Html_Template_Functions extends Edit\Html_Template_Functions
{

	//--------------------------------------------------------------------- getEditReflectionProperty
	/**
	 * Returns an HTML edit widget for current Reflection_Property object
	 *
	 * Edit widgets as search input are simpler than form edit widgets : here we remove what we don't
	 * want for search.
	 *
	 * @param $property           Reflection_Property
	 * @param $name               string
	 * @param $ignore_user        boolean ignore #User attribute, to disable invisible and read-only
	 * @param $can_always_be_null boolean ignore @null annotation and consider this can always be null
	 * @return string
	 */
	protected function getEditReflectionProperty(
		Reflection_Property $property, string $name, bool $ignore_user, bool $can_always_be_null = false
	) : string
	{
		// invisible property
		if (Store::of($property)->isFalse() || !$this->isPropertyVisible($property)) {
			return '';
		}
		// simplified property annotations for a simplified form
		Mandatory::of($property)->value                = false;
		Multiline::of($property)->value                = false;
		$property->setAnnotationLocal('editor')->value = false;
		$property->setAnnotationsLocal('user_change', []); // TODO will not work with #User_Change
		return parent::getEditReflectionProperty($property, $name, $ignore_user, $can_always_be_null);
	}

	//----------------------------------------------------------------------------- isPropertyVisible
	protected function isPropertyVisible(Reflection_Property $property) : bool
	{
		return $property->isVisible(false, false);
	}

}
