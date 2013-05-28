<?php
namespace SAF\Framework;

/**
 * The @representative annotation stores the list of properties representative of the object's value
 *
 * The __toString() method of the class should always return the representative properties values.
 *
 * @example a property called "name" could be a representative property for a unique named object
 */
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
