<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Reflection\Annotation;

/**
 * A Placeholder is a substitute expression helping to know what content to fill in a field
 *
 * @example
 * For an text field we can desire an helper like: "date + comments"
 */
class Placeholder_Annotation extends Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'placeholder';

}
