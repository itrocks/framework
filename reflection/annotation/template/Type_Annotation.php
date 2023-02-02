<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework\Reflection\Annotation;

/**
 * Type annotation : a fictive class when you need to resolve a unique class name
 * ie : Used by #Getter
 */
class Type_Annotation extends Annotation
{
	use Types_Annotation;

}
