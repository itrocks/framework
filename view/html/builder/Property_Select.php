<?php
namespace ITRocks\Framework\View\Html\Builder;

use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\View\Html\Dom\Select;

/**
 * HTML builder property select
 */
class Property_Select
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public string $name;

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property $property
	 */
	public Reflection_Property $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property Reflection_Property|null
	 * @param $name     string|null
	 */
	public function __construct(Reflection_Property $property = null, string $name = null)
	{
		if (isset($property)) $this->property = $property;
		if (isset($name))     $this->name     = $name;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return string
	 */
	public function build() : string
	{
		$properties_name = [];
		foreach ($this->property->getFinalClass()->getProperties([T_EXTENDS, T_USE]) as $property) {
			if (!$property->isStatic()) {
				$properties_name[$property->name] = $property->name;
			}
		}
		return new Select($this->name, $properties_name, $this->property->name);
	}

}
