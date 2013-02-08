<?php
namespace SAF\Framework;

/**
 * Foreignlink annotation tells which type is mapped by the property
 */
class Foreignlink_Annotation extends Documented_Type_Annotation
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
			// if @foreignlink is not set, calculates the field name using the linked class name
			$this->value = Names::setToClass(Names::classToProperty(
				Reflection_Class::getInstanceOf(
					$reflection_property->getType()->getElementTypeAsString()
				)->getAnnotation("set")->value
			));
		}
	}

}
