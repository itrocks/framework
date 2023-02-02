<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Attribute\Inheritable;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS), Inheritable]
class Override extends Class_
{

	//------------------------------------------------------------------------------------ $overrides
	/**
	 * The key is the name of an attribute
	 * The value is an argument list
	 *
	 * @var Reflection\Attribute[]
	 */
	public array $overrides;

	//-------------------------------------------------------------------------------- $property_name
	public string $property_name;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(string $property_name, Reflection\Attribute... $overrides)
	{
		$this->overrides     = $overrides;
		$this->property_name = $property_name;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->property_name . ', ' . join(', ', $this->overrides);
	}

}
