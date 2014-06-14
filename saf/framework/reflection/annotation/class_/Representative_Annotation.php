<?php
namespace SAF\Framework\Reflection\Annotation\Class_;

use SAF\Framework\PHP;
use SAF\Framework\Reflection\Annotation\Template\Class_Context_Annotation;
use SAF\Framework\Reflection\Annotation\Template\List_Annotation;
use SAF\Framework\Reflection\Interfaces\Reflection_Class;
use SAF\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * The 'representative' annotation stores the list of properties which values are representative
 * of the object.
 *
 * The __toString() method of the class should return the representative properties values.
 *
 * @example a property called 'name' could be a representative property for a unique named object
 */
class Representative_Annotation extends List_Annotation implements Class_Context_Annotation
{

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Reflection_Class
	 */
	private $class;

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
		$this->class = $class;
		if (!$this->value) {
			$this->properties = [];
			foreach ($class->getProperties([T_EXTENDS, T_USE]) as $property) {
				if (!$property->isStatic() && !$property->getType()->isMultiple()) {
					$this->properties[$property->getName()] = $property;
					$this->value[] = $property->getName();
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
			$properties = $this->class->getProperties([T_EXTENDS, T_USE]);
			foreach ($this->values() as $property_path) {
				$each = explode(DOT, $property_path);
				$property = $properties[array_shift($each)];
				foreach ($each as $property_name) {
					/** @noinspection PhpUndefinedMethodInspection Inspector bug */
					$property = $property->getType()->asReflectionClass(get_class($this->class))
						->getProperties([T_EXTENDS, T_USE])[$property_name];
				}
				$this->properties[$property_path] = $property;
			}
		}
		return $this->properties;
	}

}
