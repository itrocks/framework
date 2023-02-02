<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A 'test object' class to test links between objects with real data stored into database
 */
#[Store('test_objects')]
class Resource
{
	use Has_Name;

	//----------------------------------------------------------------------------- $mandatory_object
	public Salesman $mandatory_object;

	//------------------------------------------------------------------------------------------ $map
	/**
	 * @var Salesman[]
	 */
	public array $map;

	//------------------------------------------------------------------------------ $optional_object
	public ?Salesman $optional_object;

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
