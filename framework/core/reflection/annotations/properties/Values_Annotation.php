<?php
namespace SAF\Framework;

/**
 * The values annotation lists the values the property can take.
 *
 * The program should not be abble to give the property another value than one of the list.
 * This is useful for data controls on string[], float[] or integer[] properties.
 */
class Values_Annotation extends List_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value               string
	 * @param $reflection_property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $reflection_property)
	{
		parent::__construct($value);
		if (isset($value)) {
			$type = $reflection_property->getType();
			switch ($type->getElementTypeAsString()) {
				case Type::FLOAT:   $function = 'floatval'; break;
				case Type::INTEGER: $function = 'intval';   break;
				default:            $function = 'strval';
			}
			foreach ($this->values() as $key => $value) {
				$this->value[$key] = $function($value);
			}
		}
	}

}
