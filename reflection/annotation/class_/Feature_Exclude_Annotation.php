<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template;
use ITRocks\Framework\Reflection\Annotation\Template\Do_Not_Inherit;
use ITRocks\Framework\Reflection\Annotation\Template\Types_Annotation;

/**
 * The installation of the features will uninstall this excluded feature
 */
class Feature_Exclude_Annotation extends Annotation implements Do_Not_Inherit
{
	use Template\Feature_Annotation;
	use Types_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'feature_exclude';

}
