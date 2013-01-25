<?php
namespace SAF\Framework;

class Class_Set_Annotation extends Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 * @param $class Reflection_Class
	 */
	public function __construct($value, Reflection_Class $class)
	{
		parent::__construct($value);
		if (!$this->value) {
			$class_name = Namespaces::shortClassName($class->name);
			$this->value =
				(substr($class_name, -1) === "y") ? substr($class_name, 0, -1) . "ies" : (
				(substr($class_name, -2) === "an") ? substr($class_name, 0, -2) . "en" : (
				$class_name . "s"
			));
		}
	}

}
