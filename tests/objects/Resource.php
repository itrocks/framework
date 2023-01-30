<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Store_Name;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A 'test object' class to test links between objects with real data stored into database
 */
#[Store_Name('test_objects')]
class Resource
{
	use Has_Name;

	//----------------------------------------------------------------------------- $mandatory_object
	/**
	 * @link Object
	 * @mandatory
	 * @var Salesman
	 */
	public Salesman $mandatory_object;

	//------------------------------------------------------------------------------------------ $map
	/**
	 * @link Map
	 * @var Salesman[]
	 */
	public array $map;

	//------------------------------------------------------------------------------ $optional_object
	/**
	 * @link Object
	 * @var Salesman|null
	 */
	public Salesman|null $optional_object;

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
