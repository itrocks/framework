<?php
namespace ITRocks\Framework\Reflection\Attribute\Common;

use ITRocks\Framework\Reflection\Attribute\Class_\Implement;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;
use ITRocks\Framework\Reflection\Attribute\Template\Is_List;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Source class for both class and property Representative
 */
#[Implement(Has_Set_Final::class)]
trait Representative
{
	use Is_List { add as private parentAdd; remove as private parentRemove; }

	//---------------------------------------------------------------------------------------- $class
	private Reflection_Class $class;

	//----------------------------------------------------------------------------------- $properties
	/** @var Reflection_Property[] */
	protected array $properties;

	//------------------------------------------------------------------------------------------- add
	/** Adds a value to the annotation list of values */
	public function add(string $value) : void
	{
		if ($this->has($value)) {
			return;
		}
		$this->parentAdd($value);
		$this->properties = [];
	}

	//--------------------------------------------------------------------------------- getProperties
	/** @return Reflection_Property[] */
	public function getProperties() : array
	{
		if (isset($this->properties)) {
			return $this->properties;
		}
		$this->properties = [];
		$properties       = $this->class->getProperties([T_EXTENDS, T_USE]);
		foreach ($this->values as $property_path) {
			$each     = explode(DOT, $property_path);
			$property = $properties[array_shift($each)];
			foreach ($each as $property_name) {
				$property = $property->getType()->asReflectionClass(get_class($this->class))
					->getProperties([T_EXTENDS, T_USE])[$property_name];
			}
			$this->properties[$property_path] = $property;
		}
		return $this->properties;
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove a value and return true if the value was here and removed, false if the value
	 * already was not here
	 */
	public function remove(string $value) : bool
	{
		if (!$this->parentRemove($value)) {
			return false;
		}
		unset($this->properties);
		return true;
	}

}
