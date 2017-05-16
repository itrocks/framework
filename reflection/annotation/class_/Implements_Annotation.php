<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation\Template;
use ITRocks\Framework\Reflection\Annotation\Template\Types_Annotation;

/**
 * This must be used for traits that implement some interfaces
 * Builder will use it to implicitly add 'implements' clauses
 */
class Implements_Annotation extends Template\List_Annotation
{
	use Types_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'implements';

}
