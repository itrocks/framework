<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Reflection\Annotation;

/**
 * Reflection property @replaces annotation
 *
 * This tells the framework the property replaces an existing parent property name, so the parent
 * property and this property will point on the same reference and have a common value
 */
class Replaces_Annotation extends Annotation
{

}
