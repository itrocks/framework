<?php
namespace SAF\Framework\Reflection\Annotation\Class_;

use SAF\Framework\Reflection\Annotation\Template\List_Annotation;

/**
 * A @display_order annotation contains several values
 * It enable to define a global sort for properties display
 *
 * @example @display_order property_1 property_2 property_3
 */
class Display_Order_Annotation extends List_Annotation
{
	const ANNOTATION = 'display_order';

}
