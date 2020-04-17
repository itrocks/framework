<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Do_Not_Inherit;
use ITRocks\Framework\Reflection\Annotation\Template\Multiple_Annotation;

/**
 * feature [[[Class/Path/]feature] Human-readable atomic end-user feature name]
 *
 * This is a Multiple_Annotation
 * Marks the class as an atomic end-user feature
 * Implicit end-user features will be enabled for this class if there are no yaml files
 */
class Feature_Annotation extends Annotation implements Do_Not_Inherit, Multiple_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'feature';

}
