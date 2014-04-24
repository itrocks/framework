<?php
namespace SAF\Framework\Reflection\Annotation\Class_;

use SAF\Framework\Reflection\Annotation\Template\List_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Types_Annotation;

/**
 * This must be used for interfaces that are designed to use a given class
 *
 * TODO NORMAL is it used at any time ?
 */
class Use_Annotation extends List_Annotation
{
	use Types_Annotation;

}
