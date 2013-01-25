<?php
namespace SAF\Framework;

class Var_Annotation extends Documented_Type_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 *
	 * @param $value string
	 * @param $reflection_object Reflection_Property
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
