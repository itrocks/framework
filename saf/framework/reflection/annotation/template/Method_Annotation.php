<?php
namespace SAF\Framework\Reflection\Annotation\Template;

use SAF\Framework\PHP\Reflection_Class;
use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Interfaces\Reflection;
use SAF\Framework\Reflection\Interfaces\Reflection_Property;
use SAF\Framework\Tools\Names;

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
class Method_Annotation extends Annotation implements Reflection_Context_Annotation
{

	//--------------------------------------------------------------------------------------- $static
	/**
	 * @var boolean
	 */
	public $static = false;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value           string
	 * @param $class_property  Reflection
	 * @param $annotation_name string
	 */
	public function __construct($value, Reflection $class_property, $annotation_name)
	{
		if (!empty($value)) {
			$class = ($class_property instanceof Reflection_Property)
				? $class_property->getFinalClass()
				: $class_property;
			if ($pos = strpos($value, '::')) {
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
				$this->static = true;
			}
			else {
				if ($value === true) {
					$value = Names::propertyToMethod($annotation_name);
				}
				$value = $class->getName() . '::' . $value;
			}
		}
		parent::__construct($value);
	}

	//------------------------------------------------------------------------------------------ call
	/**
	 * The $object argument will be the first argument before $arguments in case of a static call
	 * If the value is a method for the current object, only $arguments will be sent
	 *
	 * @param $object    object|string the object will be the first. If string, this is a class name
	 * @param $arguments array
	 * @return mixed the value returned by the called method
	 */
	public function call($object, $arguments = [])
	{
		if ($this->static || is_string($object)) {
			return call_user_func_array($this->value, array_merge([$object], $arguments));
		}
		return call_user_func_array([$object, rParse($this->value, '::')], $arguments);
	}

}
