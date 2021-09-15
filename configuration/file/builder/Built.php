<?php
namespace ITRocks\Framework\Configuration\File\Builder;

/**
 * Built class
 */
abstract class Built
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public string $class_name;

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

}
