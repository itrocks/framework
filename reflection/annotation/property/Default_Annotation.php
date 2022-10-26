<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\Constant_Or_Method_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Type;

/**
 * Property annotation default
 *
 * @default [[\Class\Namespace\]Class_Name::]methodName
 * Identifies a method that gets the default value for the property.
 * The Property will be sent as an argument to this callable.
 *
 * If no @default annotation is set for an object property, look at the class @default annotation
 */
class Default_Annotation extends Constant_Or_Method_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value           bool|string|null
	 * @param $class_property  Reflection|Reflection_Property
	 * @param $annotation_name string
	 */
	public function __construct(
		bool|string|null $value, Reflection|Reflection_Property $class_property, string $annotation_name
	) {
		if (!$value) {
			$type = $class_property->getType();
			if ($type->isClass() && ($type->getElementTypeAsString() !== Type::OBJECT)) {
				$class = $type->asReflectionClass();
				$value = $class->getAnnotation('default')->value;
				if ($value && str_contains($value, '::') && !str_starts_with($value, BS)) {
					$value = BS . $value;
				}
			}
		}
		parent::__construct($value, $class_property, $annotation_name);
	}

}
