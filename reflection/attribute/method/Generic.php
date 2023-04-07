<?php
namespace ITRocks\Framework\Reflection\Attribute\Method;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;

/**
 * A generic method is a method associated to any property. It's arguments should begin by :
 * - if static : object $object, string $property_name
 * - if non-static : string $property_name
 * Because it's generic, it may need to identify the context.
 */
#[Attribute(Attribute::TARGET_METHOD), Inheritable]
class Generic
{
	use Common;

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return '#Generic';
	}

}
