<?php
namespace SAF\Framework;

/**
 * The 'representative' annotation stores the list of properties which values are representative
 * of the object.
 *
 * The __toString() method of the class should return the representative properties values.
 *
 * @example a property called 'name' could be a representative property for a unique named object
 */
class Class_Representative_Annotation extends List_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Builds representative annotation content
	 *
	 * Default representative is the list of non-static properties of the class
	 *
	 * @param $value string
	 * @param $class Reflection_Class
	 */
	public function __construct($value, Reflection_Class $class)
	{
		parent::__construct($value, $class);
		if (!$this->value) {
			foreach ($class->getAllProperties() as $property) {
				$link = $property->getAnnotation('link')->value;
				if (!$property->isStatic() && ($link !== 'Collection') && ($link !== 'Map')) {
					$this->value[] = $property->name;
				}
			}
		}
	}

}
