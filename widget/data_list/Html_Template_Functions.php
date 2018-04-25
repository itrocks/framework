<?php
namespace ITRocks\Framework\Widget\Data_List;

use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Widget\Edit;
use ITRocks\Framework\Widget\Validate\Property\Mandatory_Annotation;

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
	 * @param $ignore_user        boolean ignore @user annotation, to disable invisible and read-only
	 * @param $can_always_be_null boolean ignore @null annotation and consider this can always be null
	 * @return string
	 */
	protected function getEditReflectionProperty(
		Reflection_Property $property, $name, $ignore_user, $can_always_be_null = false
	) {
		// invisible property
		if (Store_Annotation::of($property)->isFalse() || !$this->isPropertyVisible($property)) {
			return '';
		}
		// simplified property annotations for a simplified form
		Mandatory_Annotation::local($property)->value     = false;
		$property->setAnnotationLocal('editor')->value    = false;
		$property->setAnnotationLocal('multiline')->value = false;
		$property->setAnnotationsLocal('user_change', []);
		return parent::getEditReflectionProperty($property, $name, $ignore_user, $can_always_be_null);
	}

	//----------------------------------------------------------------------------- isPropertyVisible
	/**
	 * @param $property Reflection_Property
	 * @return boolean
	 */
	protected function isPropertyVisible(Reflection_Property $property)
	{
		return $property->isVisible();
	}

}
