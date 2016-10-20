<?php
namespace SAF\Framework\Widget\Validate\Annotation;

use SAF\Framework\Reflection\Annotation\Template\Method_Annotation;

/**
 * Common code for all @validate annotations
 */
class Validate_Annotation extends Method_Annotation
{
	use Has_Message;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'validate';

}
