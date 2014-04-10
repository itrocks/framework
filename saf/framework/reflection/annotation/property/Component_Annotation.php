<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Reflection\Annotation\Template\Boolean_Annotation;

/**
 * The object referenced by the property is a component of the main object.
 * It should not exist without it's container.
 *
 * @todo not sure this is used anymore
 */
class Component_Annotation extends Boolean_Annotation
{

}
