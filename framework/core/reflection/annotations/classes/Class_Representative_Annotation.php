<?php
namespace SAF\Framework;

class Class_Representative_Annotation extends List_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Builds representative annotation content
	 *
	 * Default representative is the full list of properties from the object
	 *
	 * @param $value string
	 * @param $class Reflection_Class
	 */
	public function __construct($value, Reflection_Class $class)
	{
		parent::__construct($value, $class);
		if (!$this->value) {
			$this->value = array_keys($class->getAllProperties());
		}
	}

}
