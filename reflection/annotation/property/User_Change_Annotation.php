<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection;

/**
 * Associates a feature controller to call each time a property value is changed by the final user
 * to an input form.
 *
 * a target selector can be used to define where the result is loaded (#messages as default)
 *
 * @user_change [[\Vendor\Module\]Class_Name::]featureName] [target_selector]
 */
class User_Change_Annotation extends Method_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'user_change';

	//--------------------------------------------------------------------------------------- $target
	/**
	 * @var string
	 */
	public $target = null;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value           string
	 * @param $class_property  Reflection
	 * @param $annotation_name string
	 */
	public function __construct($value, Reflection $class_property, $annotation_name)
	{
		if (strpos($value, SP)) {
			list($value, $this->target) = explode(SP, $value, 2);
		}
		parent::__construct($value, $class_property, $annotation_name);
	}

}
