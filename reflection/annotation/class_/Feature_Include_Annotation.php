<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation\Template;
use ITRocks\Framework\Reflection\Annotation\Template\Constant_Or_Type_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Do_Not_Inherit;

/**
 * The installation of the features will install this included feature
 */
class Feature_Include_Annotation extends Constant_Or_Type_Annotation implements Do_Not_Inherit
{
	use Template\Feature_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'feature_include';

}
