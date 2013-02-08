<?php
namespace SAF\Framework;

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
				// if @foreign is not set, gets the property which has @composite set from the foreign class
				if ($type->usesTrait('SAF\Framework\Component')) {
					$class = Reflection_Class::getInstanceOf($type->getElementTypeAsString());
					$parent = $class->getAnnotedProperty("composite");
					if (isset($parent)) {
						$this->value = $parent->name;
					}
				}
				if (!$this->value) {
					$this->value = Names::classToProperty($reflection_property->class);
				}
			}
		}
	}

}
