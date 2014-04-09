<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Tools\Namespaces;

/**
 * Tells the remover which method must be called to remove a component object from a composite class
 * for this property.
 *
 * The remover must be a current object's method, or a static method from another class.
 *
 * This can be used into classes that use trait Remover only.
 */
class Remover_Annotation extends Annotation implements Property_Context_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string
	 * @param $property Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct($value, Reflection_Property $property)
	{
		parent::__construct(Namespaces::defaultFullClassName($value, $property->class));
	}

}
