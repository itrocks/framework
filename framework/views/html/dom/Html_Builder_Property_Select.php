<?php
namespace SAF\Framework;

class Html_Builder_Property_Select
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
		$properties_name = array();
		foreach ($this->property->getFinalClass()->getAllProperties() as $property) {
			if (!$property->isStatic()) {
				$properties_name[$property->name] = $property->name;
			}
		}
		return new Html_Select($this->name, $properties_name, $this->property->name);
	}

}
