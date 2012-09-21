<?php
namespace SAF\Framework;

class Var_Annotation extends Documented_Type_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * 
	 * @param string $value
	 * @param Reflection_Property $reflection_object
	 */
	public function __construct($value, Reflection_Property $reflection_object)
	{
		parent::__construct($value, $reflection_object);
		if (!$this->value) {
			$types = $reflection_object->getDeclaringClass()->getDefaultProperties();
			$this->value = gettype($types[$reflection_object->name]);
		}
	}

}
