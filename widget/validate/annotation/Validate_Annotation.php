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

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Gets the value of the property from the last validated object
	 * In case of a validate annotation, we do not want to output in the template the value that
	 * represents a method name. So we disable output
	 *
	 * @return null
	 */
	public function getValue()
	{
		return null;
	}

}
