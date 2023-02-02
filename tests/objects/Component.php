<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Mapper;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A component
 */
#[Store('test_components')]
class Component
{
	use Has_Name;
	use Mapper\Component;

	//------------------------------------------------------------------------------------ $composite
	#[Property\Composite]
	public Composite $composite;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * A constructor for your Has_Name class
	 *
	 * @todo use With_Constructor : needs AOP compiler update
	 */
	public function __construct(string $name = null)
	{
		if (isset($name)) {
			$this->name = $name;
		}
	}

}
