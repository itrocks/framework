<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Traits\Has_Name;

/**
 * A simple generic counter manager
 *
 * @extends Has_Name
 * @see Has_Name
 * @store_name test_counters
 */
class Counter
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var integer
	 */
	private $value = 0;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 */
	public function __construct($class_name = null)
	{
		if (isset($class_name)) {
			$this->class_name = $class_name;
		}
	}

	//------------------------------------------------------------------------------------- increment
	/**
	 * @return integer
	 */
	public function increment()
	{
		return ++ $this->value;
	}

}
