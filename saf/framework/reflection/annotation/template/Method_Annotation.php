<?php
namespace SAF\Framework\Reflection\Annotation\Template;

use SAF\Framework\PHP\Reflection_Class;
use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * This annotation template contains a callable method :
 * - "methodName" to call self::methodName()
 * - "Class_Name::methodName" to call My\Namespace\Class_Name::methodName()
 * - "\Another\Namespace\Class_Name::methodName" to call Another\Namespace\Class_Name::methodName()
 * - "Outside_Class_Name::methodName()" to call Another\Namespace\Outside_Class_Name::methodName()
 *   needs a use clause to be defined like this : "use Another\Namespace\Outside_Class_Name;"
 *
 * Used by Property\Default_Annotation, Property\Getter_Annotation, Property\Setter_Annotation
 */
class Method_Annotation extends Annotation implements Property_Context_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value   string
	 * @param $property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $property)
	{
		if (!empty($value)) {
			if ($pos = strpos($value, '::')) {
				$class           = $property->getFinalClass();
				$type_annotation = new Type_Annotation(substr($value, 0, $pos), $class);
				$type_annotation->applyNamespace($class->getNamespaceName());
				if (!@class_exists($type_annotation->value)) {
					$type_annotation->value = substr($value, 0, $pos);
					$type_annotation->applyNamespace(
						$class->getNamespaceName(),
						Reflection_Class::of($class->getName())->getNamespaceUse()
					);
				}
				$value = $type_annotation->value . substr($value, $pos);
			}
			else {
				$value = $property->getFinalClassName() . '::' . $value;
			}
		}
		parent::__construct($value);
	}

}
