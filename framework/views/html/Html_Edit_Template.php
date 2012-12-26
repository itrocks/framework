<?php
namespace SAF\Framework;

class Html_Edit_Template extends Html_Template
{

	//-------------------------------------------------------------------------------------- parseVar
	protected function parseVar($objects, $var_name)
	{
		$property = reset($objects);
		$value = parent::parseVar($objects, $var_name);
		return (($var_name == "value") && ($property instanceof Reflection_Property))
			? (new Html_Builder_Property($property, $value))->build()
			: $value;
	}

}
