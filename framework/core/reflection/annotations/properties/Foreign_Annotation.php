<?php
namespace SAF\Framework;

/**
 * The property name into the foreign class that contains current object
 *
 * If the property has no "foreign" annotation, default will be :
 * - If the class type of the property is a Component with a "composite" property having the master class as type, the composite property name of the foreign class will be taken
 * - If the class type is not a Component or has no "composite" field, the property of the foreign class that has the master class as type will be taken
 * - If zero or multiple properties have the class as type, you must use the "foreign" annotation to identify the property tu be used or this will have unpredictable result !
 */
class Foreign_Annotation extends Documented_Type_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value               string
	 * @param $reflection_property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $reflection_property)
	{
		parent::__construct($value);
		if (!$this->value) {
			$type = $reflection_property->getType();
			if ($type->isMultiple() && $type->isClass()) {
				// if @foreign is not set, try to get defaults...
				$foreign_class = Reflection_Class::getInstanceOf($type->getElementTypeAsString());
				if ($type->usesTrait('SAF\Framework\Component')) {
					// gets the property which has @composite set from the foreign class
					$this->value = $this->scanProperties(
						$reflection_property, $foreign_class->getAnnotedProperties("composite")
					);
				}
				if (!$this->value) {
					// gets the property which type is superclass of the main class
					$this->value = $this->scanProperties(
						$reflection_property, $foreign_class->getAllProperties()
					);
				}
				if (!$this->value) {
					// build an arbitrary name built from the set class name of the main property class name
					$this->value = Names::classToProperty(Names::setToClass(
						$reflection_property->getDeclaringClass()->getAnnotation("set")->value
					));
				}
			}
		}
	}

	//-------------------------------------------------------------------------------- scanProperties
	/**
	 * @param $reflection_property Reflection_Property
	 * @param $foreign_properties  Reflection_Property[]
	 * @return string|null
	 */
	private function scanProperties($reflection_property, $foreign_properties)
	{
		$value = null;
		foreach ($foreign_properties as $foreign_property) {
			if (class_instanceof($reflection_property->class, $foreign_property->getType()->asString())) {
				if (empty($value)) {
					$value = $foreign_property->name;
				}
				else {
					trigger_error(
						$reflection_property->class . '::$' . $reflection_property->name
							. ": ambigous foreign properties " . $this->value
							. " and " . $foreign_property->name . " match class",
						E_USER_ERROR
					);
					$value = null;
					break;
				}
			}
		}
		return $value;
	}

}
