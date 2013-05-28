<?php
namespace SAF\Framework;

/**
 * The @use annotation gives the list of the overridden properties which phpdoc must be used
 * to get annotations.
 *
 * If you override a property into a child class without setting the @set annotation,
 * the annotations of the property will be ignored,
 * and only the root class property annotations will be recognized by reflections methods.
 *
 * Property names are accepted with or without the "$" character.
 *
 * @example @use property_1, $property_2, property_3
 */
class Class_Use_Annotation extends List_Annotation implements Multiple_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 */
	public function __construct($value)
	{
		parent::__construct($value);
		foreach ($this->values() as $key => $val) {
			if (substr($val, 0, 1) == "$") {
				$this->value[$key] = substr($val, 1);
			}
		}
	}

}
