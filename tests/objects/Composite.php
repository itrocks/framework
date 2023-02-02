<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A sample composite class
 */
#[Store('test_composites')]
class Composite
{
	use Has_Name;

	//------------------------------------------------------------------------------------ $component
	#[Property\Component]
	public ?Component $component;

	//----------------------------------------------------------------------------------- $components
	/**
	 * @var Component[]
	 */
	#[Property\Component]
	public array $components;

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
