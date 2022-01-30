<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Types_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Tools\Names;

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

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'set';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value ?string
	 * @param $class Reflection_Class
	 */
	public function __construct(?string $value, Reflection_Class $class)
	{
		parent::__construct($value);
		if (!$this->value) {
			$class_name  = $class->getName();
			$this->value = Names::singleToSet($class_name);
		}
	}

}
