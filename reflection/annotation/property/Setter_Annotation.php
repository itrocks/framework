<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;

/**
 * @setter [[[\Vendor\Module\]Class_Name::]methodName]
 * This is a Multiple_Annotation
 * Tells a method name that is the setter for that property.
 * The setter will be called each time the program changes the value of the property.
 */
class Setter_Annotation extends Method_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'setter';

	//---------------------------------------------------------------------------------------- PREFIX
	const PREFIX = 'set';

}
