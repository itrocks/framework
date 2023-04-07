<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS), Inheritable]
class Override
{
	use Common;

	//------------------------------------------------------------------------------------ $overrides
	/**
	 * The key is the name of an attribute
	 * The value is a method/property attribute
	 *
	 * @var Common[]
	 */
	public array $overrides;

	//-------------------------------------------------------------------------------- $property_name
	public string $property_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocSignatureInspection $overrides
	 * @param $property_name string
	 * @param ...$overrides  Common
	 */
	public function __construct(string $property_name, object... $overrides)
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
