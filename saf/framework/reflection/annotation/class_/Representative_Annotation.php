<?php
namespace SAF\Framework\Reflection\Annotation\Class_;

use SAF\Framework\Reflection\Annotation\Template\List_Annotation;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property;

/**
 * The 'representative' annotation stores the list of properties which values are representative
 * of the object.
 *
 * The __toString() method of the class should return the representative properties values.
 *
 * @example a property called 'name' could be a representative property for a unique named object
 */
class Representative_Annotation extends List_Annotation
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	private $class_name;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var Reflection_Property[]
	 */
	private $properties;

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
		$this->class_name = $class->name;
		if (!$this->value) {
			$this->properties = [];
			foreach ($class->getAllProperties() as $property) {
				$link = $property->getAnnotation('link')->value;
				if (!$property->isStatic() && ($link !== 'Collection') && ($link !== 'Map')) {
					$this->properties[$property->name] = $property;
					$this->value[] = $property->name;
				}
			}
		}
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @return Reflection_Property[]
	 */
	public function getProperties()
	{
		if (!isset($this->properties)) {
			$this->properties = [];
			foreach ($this->values() as $property_path) {
				$this->properties[$property_path] = new Reflection_Property(
					$this->class_name, $property_path
				);
			}
		}
		return $this->properties;
	}

}
