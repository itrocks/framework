<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation\Template;
use ITRocks\Framework\Reflection\Annotation\Template\Do_Not_Inherit;
use ITRocks\Framework\Reflection\Annotation\Template\Types_Annotation;

/**
 * The installation of the features will activate a new plugin into config.php
 *
 * The property $value contains a list of plugin classes
 *
 * @override $value @var ?string[]
 * @property ?string[] value
 */
class Feature_Plugin_Annotation extends Template\List_Annotation implements Do_Not_Inherit
{
	use Template\Feature_Annotation;
	use Types_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'feature_plugin';

}
