<?php
namespace ITRocks\Framework\Reflection\Attribute\Template;

use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Tools\Names;

trait Property_Last
{

	//-------------------------------------------------------------------------------- getDefaultName
	function getDefaultName(Reflection_Property $property) : string
	{
		$attribute = Names::classToProperty(trim(rLastParse(get_class($this), BS), '_'));
		return Names::propertyToMethod($attribute . '_' . $property->getName());
	}

}
