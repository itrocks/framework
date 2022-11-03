<?php
namespace ITRocks\Framework\Mapper;

/**
 * Property path mapper methods for classes
 */
class Class_Property_Path
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	protected string $class_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string class name
	 */
	public function __construct(string $class_name)
	{
		$this->class_name = $class_name;
	}

}
