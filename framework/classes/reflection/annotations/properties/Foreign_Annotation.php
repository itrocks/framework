<?php
namespace SAF\Framework;

class Foreign_Annotation extends Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param string $value
	 * @param Reflection_Property $reflection_object
	 */
	public function __construct($value, Reflection_Property $reflection_object)
	{
		parent::__construct($value);
		if (!$this->value) {
			$this->value = Names::classToProperty($reflection_object->class);
		}
	}

}
