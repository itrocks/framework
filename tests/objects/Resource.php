<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Traits\Has_Name;

/**
 * A 'test object' class to test links between objects with real data stored into database
 *
 * @store_name test_objects
 */
class Resource
{
	use Has_Name;

	//----------------------------------------------------------------------------- $mandatory_object
	/**
	 * @link Object
	 * @mandatory
	 * @var Salesman
	 */
	public $mandatory_object;

	//------------------------------------------------------------------------------------------ $map
	/**
	 * @link Map
	 * @var Salesman[]
	 */
	public $map;

	//------------------------------------------------------------------------------ $optional_object
	/**
	 * @link Object
	 * @var Salesman
	 */
	public $optional_object;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Object constructor
	 *
	 * @param $name string
	 */
	public function __construct($name = null)
	{
		if (isset($name)) $this->name = $name;
	}

}
