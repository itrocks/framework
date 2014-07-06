<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Annotation\Template\Types_Annotation;

/**
 * Use another class than Widget\Edit\Html_Builder_Property to parse the property value
 */
class Edit_Annotation extends Annotation
{
	use Types_Annotation;

}
