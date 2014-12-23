<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Mapper\Getter;
use SAF\Framework\PHP\Reflection_Class;
use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Type_Annotation;
use SAF\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Tells a method name that is the getter for that property.
 *
 * The getter will be called each time the program accesses the property.
 * When there is a @link annotation and no @getter, a defaut @getter is set with the Dao access
 * common method depending on the link type.
 */
class Getter_Annotation extends Annotation implements Property_Context_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value   string
	 * @param $property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $property)
	{
		parent::__construct($value);
		if ($pos = strpos($this->value, '::')) {
			$class = $property->getFinalClass();
			$type_annotation = new Type_Annotation(substr($this->value, 0, $pos), $class);
			$type_annotation->applyNamespace($class->getNamespaceName());
			if (!@class_exists($type_annotation->value)) {
				$type_annotation->value = substr($this->value, 0, $pos);
				$type_annotation->applyNamespace(
					$class->getNamespaceName(),
					Reflection_Class::of($class->getName())->getNamespaceUse()
				);
			}
			$this->value = $type_annotation->value . substr($this->value, $pos);
		}
		elseif (empty($this->value)) {
			$link = ($property->getAnnotation('link')->value);
			if (!empty($link)) {
				$this->value = Getter::class . '::get' . $link;
			}
		}
	}

}
