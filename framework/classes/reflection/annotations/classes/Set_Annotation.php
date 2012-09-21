<?php
namespace SAF\Framework;

class Set_Annotation extends Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param string $value
	 * @param Reflection_Class $reflection_object
	 */
	public function __construct($value, Reflection_Class $reflection_object)
	{
		parent::__construct($value);
		if (!$this->value) {
			$this->value = Names::classToSet($reflection_object->name);
		}
	}

}
