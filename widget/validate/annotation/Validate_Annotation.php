<?php
namespace ITRocks\Framework\Widget\Validate\Annotation;

use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;

/**
 * Common code for all @validate annotations
 */
abstract class Validate_Annotation extends Method_Annotation
{
	use Has_Message;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'validate';

}
