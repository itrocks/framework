<?php
namespace ITRocks\Framework\Feature\Validate\Annotation;

use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;

/**
 * Common code for all @validate annotations
 *
 * @override value @user_getter getValue
 */
abstract class Validate_Annotation extends Method_Annotation
{
	use Has_Message;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'validate';

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Used from template. We do not want to output in the template the $value that
	 * represents a method name. So we disable standard output of {value} or <!--value-->
	 */
	public function getValue() : null
	{
		return null;
	}

}
