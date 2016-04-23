<?php
namespace SAF\Framework\Reflection\Annotation\Class_;

use SAF\Framework\Reflection\Annotation\Template\List_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Types_Annotation;

/**
 * This must be used for traits that are designed to extend a given class
 * Builder will use it to sort built classes
 */
class Extends_Annotation extends List_Annotation
{
	use Types_Annotation;

}
