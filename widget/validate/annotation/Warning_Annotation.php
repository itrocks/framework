<?php
namespace ITRocks\Framework\Widget\Validate\Annotation;

use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;

/**
 * Common code for all @warning annotations
 */
abstract class Warning_Annotation extends Method_Annotation
{
	use Has_Message;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'warning';

}
