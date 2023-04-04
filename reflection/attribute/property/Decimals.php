<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Property;

/**
 * Tells how many decimals are stored/displayed on a float number
 *
 * @example With 4 decimal floats, 5.77966 will be displayed rounded to 5.7797
 */
#[Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Decimals extends Property
{

	//---------------------------------------------------------------------------------------- $value
	public int $value;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(int $value)
	{
		$this->value = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return strval($this->value);
	}

}
