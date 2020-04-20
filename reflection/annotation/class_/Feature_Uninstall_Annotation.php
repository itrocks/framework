<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation\Template;
use ITRocks\Framework\Reflection\Annotation\Template\Do_Not_Inherit;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * Declares a method to be called during feature installation
 *
 * This method (uninstallFeature is the default value if empty) will be called each time a feature
 * is uninstalled
 */
class Feature_Uninstall_Annotation extends Method_Annotation implements Do_Not_Inherit
{
	use Template\Feature_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'feature_uninstall';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value           string
	 * @param $class           Reflection|Reflection_Class The contextual Reflection_Class object
	 * @param $annotation_name string
	 */
	public function __construct($value, Reflection $class, $annotation_name)
	{
		if (static::$context) {
			$class = static::$context;
		}
		parent::__construct($value, $class, $annotation_name);
	}

}
