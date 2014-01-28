<?php
namespace SAF\Framework;

/**
 * Before function joinpoint
 */
class Before_Function_Joinpoint
{

	//--------------------------------------------------------------------------------------- $advice
	/**
	 * @var string[]|object[]|string
	 */
	public $advice;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $function_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $function_name string
	 * @param $advice        string[]|object[]|string
	 */
	public function __construct($function_name, $advice)
	{
		$this->function_name = $function_name;
		$this->advice        = $advice;
	}

}
