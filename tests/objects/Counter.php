<?php
namespace ITRocks\Framework\Tests\Objects;

/**
 * A simple generic counter manager
 *
 * @store_name test_counters
 */
class Counter
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public string $class_name = '';

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var integer
	 */
	private int $value = 0;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string|null
	 */
	public function __construct(string $class_name = null)
	{
		if (isset($class_name)) {
			$this->class_name = $class_name;
		}
	}

	//------------------------------------------------------------------------------------- increment
	/**
	 * @return integer
	 */
	public function increment() : int
	{
		return ++ $this->value;
	}

}
