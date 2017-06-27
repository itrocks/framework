<?php
namespace ITRocks\Framework\Widget\Data_List;

use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\View\Html\Dom\Element;
use ITRocks\Framework\Widget\Edit;

/**
 * HTML template functions for data list
 */
class Html_Template_Functions extends Edit\Html_Template_Functions
{

	//--------------------------------------------------------------------- getEditReflectionProperty
	/**
	 * Returns an HTML edit widget for current Reflection_Property object
	 *
	 * @param $property    Reflection_Property
	 * @param $name        string
	 * @param $ignore_user boolean ignore @user annotation, to disable invisible and read-only
	 * @return string
	 */
	protected function getEditReflectionProperty(Reflection_Property $property, $name, $ignore_user)
	{
		if (Store_Annotation::of($property)->isFalse() || !$this->isPropertyVisible($property)) {
			return '';
		}
		$edit = parent::getEditReflectionProperty($property, $name, $ignore_user);
		// #97326 having data-on-change on filter input is undesirable. This is only for object edition!
		if ($edit instanceof Element) {
			$edit->removeAttribute('data-on-change');
		}
		else {
			$edit = preg_replace('/data-on-change="[^"]*"/', '', strval($edit));
		}
		return strval($edit);
	}

}
