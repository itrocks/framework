<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation\Template;
use ITRocks\Framework\Reflection\Annotation\Template\Do_Not_Inherit;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;

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

}
