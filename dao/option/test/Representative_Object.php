<?php
namespace ITRocks\Framework\Dao\Option\Test;

use ITRocks\Framework\Reflection\Attribute\Class_\Representative;

/**
 * Class Representative_Object
 */
#[Representative('name_value')]
class Representative_Object
{

	//----------------------------------------------------------------------------------------- $name
	public string $name;

	//----------------------------------------------------------------------------------- $name_value
	public string $name_value;

}
