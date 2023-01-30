<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Store_Name;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A sample composite class
 */
#[Store_Name('test_composites')]
class Composite
{
	use Has_Name;

	//------------------------------------------------------------------------------------ $component
	/**
	 * @component
	 * @link Object
	 * @var ?Component
	 */
	public ?Component $component;

	//----------------------------------------------------------------------------------- $components
	/**
	 * @link Collection
	 * @var Component[]
	 */
	public array $components;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * A constructor for your Has_Name class
	 *
	 * @param $name string|null
	 * @todo use With_Constructor : needs AOP compiler update
	 */
	public function __construct(string $name = null)
	{
		if (isset($name)) {
			$this->name = $name;
		}
	}

}
