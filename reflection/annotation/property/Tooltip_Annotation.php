<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\Constant_Or_Method_Annotation;

/**
 * A Tooltip is a text to help the user to know how to fill in a form field
 *
 * @example @tooltip my text that will be translated
 */
class Tooltip_Annotation extends Constant_Or_Method_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'tooltip';

}
