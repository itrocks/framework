<?php
namespace SAF\Framework;

/**
 * Foreignlink annotation tells which type is mapped by the property
 */
class Foreignlink_Annotation extends Annotation
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
			$this->value = $reflection_property->getType()->getElementTypeAsString();
		}
	}

}
