<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Annotation\Template\Class_Context_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Representative;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;

/**
 * The 'representative' annotation stores the list of properties which values are representative
 * of the object.
 *
 * The __toString() method of the class should return the representative properties values.
 *
 * @example a property called 'name' could be a representative property for a unique named object
 */
class Representative_Annotation extends Representative implements Class_Context_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'representative';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Builds representative annotation content
	 *
	 * Default representative is the list of non-static properties of the class
	 *
	 * @param $value ?string
	 * @param $class Reflection_Class
	 */
	public function __construct(?string $value, Reflection_Class $class)
	{
		parent::__construct($value, $class);
		if (!$this->value) {
			$this->properties = [];
			foreach ($class->getProperties([T_EXTENDS, T_USE]) as $property) {
				if (!$property->isStatic() && !$property->getType()->isMultiple()) {
					$this->properties[$property->getName()] = $property;
				}
			}
		}
		$this->properties = Replaces_Annotations::replaceProperties($this->getProperties());
		$this->value      = array_keys($this->properties);
	}

}
