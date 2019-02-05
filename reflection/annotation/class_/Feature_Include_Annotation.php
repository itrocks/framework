<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Types_Annotation;

/**
 * The installation of the features will install this included feature
 */
class Feature_Include_Annotation extends Annotation
{
	use Types_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'feature_include';

}
