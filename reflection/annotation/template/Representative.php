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
					/** @noinspection PhpUndefinedMethodInspection Inspector bug */
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

}
