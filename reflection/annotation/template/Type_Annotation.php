<?php
namespace SAF\Framework\Reflection\Annotation\Template;

use SAF\Framework\Reflection\Annotation;

/**
 * Type annotation : a fictive class when you need to resolve a unique class name
 * ie : Used by Getter_Annotation
 */
class Type_Annotation extends Annotation
{
	use Types_Annotation;

}
