<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Source class for both class and property Representative_Annotation
 */
class Representative extends List_Annotation
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
	protected $properties;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Builds representative annotation content
	 *
	 * @param $value string
	 * @param $class Reflection_Class
	 */
	public function __construct($value, Reflection_Class $class)
	{
		parent::__construct($value);
		$this->class = $class;
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds a value to the annotation list of values
	 *
	 * @param $value string
	 */
	public function add($value)
	{
		if (!$this->has($value)) {
			parent::add($value);
			$this->properties = null;
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
			$properties       = $this->class->getProperties([T_EXTENDS, T_USE]);
			foreach ($this->values() as $property_path) {
				$each     = explode(DOT, $property_path);
				$property = $properties[array_shift($each)];
				foreach ($each as $property_name) {
					$property = $property->getType()->asReflectionClass(get_class($this->class))
						->getProperties([T_EXTENDS, T_USE])[$property_name];
				}
				$this->properties[$property_path] = $property;
			}
		}
		return $this->properties;
	}

	//------------------------------------------------------------------------------ getPropertyNames
	/**
	 * @return string[]
	 */
	public function getPropertyNames()
	{
		return $this->value;
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove a value and return true if the value was here and removed, false if the value
	 * already was not here
	 *
	 * @param $value string
	 * @return boolean
	 */
	public function remove($value)
	{
		if (parent::remove($value)) {
			$this->properties = null;
			return true;
		}
		return false;
	}

}
