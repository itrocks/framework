<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\Constant_Or_Method_Annotation;

/**
 * A Placeholder is a substitute expression helping to know what content to fill in a field
 * Value of annotation should be a method name for dynamic result or a string for static text
 *
 * @example
 * * @placeholder my text that will be translated
 * * @placeholder myMethodName
 * * @placeholder self::myStaticMethodName
 * * @placeholder Class_Name::anotherMethodName
 */
class Placeholder_Annotation extends Constant_Or_Method_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'placeholder';

}
