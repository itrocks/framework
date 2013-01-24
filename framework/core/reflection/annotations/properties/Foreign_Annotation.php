<?php
namespace SAF\Framework;

class Foreign_Annotation extends Annotation
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
			$this->value = Names::classToProperty($reflection_property->class);
		}
	}

}
