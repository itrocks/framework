<?php
namespace SAF\Framework\Reflection\Annotation\Class_;

use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Annotation\Template\Types_Annotation;
use SAF\Framework\Reflection\Interfaces\Reflection_Class;
use SAF\Framework\Tools\Set;

/**
 * Use the @set annotation to define the name of the set class (virtual or real) associated to the
 * business object class
 *
 * If the annotation is not specified by the programmer, a default value is calculated, adding an
 * 's' to the name of the class
 *
 * @example @set Class_Sets_Annotations
 */
class Set_Annotation extends Annotation
{
	use Types_Annotation;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 * @param $class Reflection_Class
	 */
	public function __construct($value, Reflection_Class $class)
	{
		parent::__construct($value);
		if (!$this->value) {
			$class_name = $class->getName();
			$this->value = Set::defaultSetClassNameOf($class_name);
		}
	}

}
