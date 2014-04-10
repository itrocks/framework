<?php
namespace SAF\Framework\View\Html\Builder;

use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\View\Html\Dom\Select;

/**
 * HTML builder property select
 */
class Property_Select
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property $property
	 */
	public $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property Reflection_Property
	 * @param $name     string
	 */
	public function __construct(Reflection_Property $property = null, $name = null)
	{
		if (isset($property)) $this->property = $property;
		if (isset($name))     $this->name     = $name;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return string
	 */
	public function build()
	{
		$properties_name = [];
		foreach ($this->property->getFinalClass()->getAllProperties() as $property) {
			if (!$property->isStatic()) {
				$properties_name[$property->name] = $property->name;
			}
		}
		return new Select($this->name, $properties_name, $this->property->name);
	}

}
