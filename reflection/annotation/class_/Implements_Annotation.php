<?php
namespace SAF\Framework\Reflection\Annotation\Class_;

use SAF\Framework\Reflection\Annotation\Template\List_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Types_Annotation;

/**
 * This must be used for traits that implement some interfaces
 * Builder will use it to implicitly add 'implements' clauses
 */
class Implements_Annotation extends List_Annotation
{
	use Types_Annotation;

}
