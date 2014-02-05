<?php
namespace SAF\AOP;

/**
 * Aspect weaver properties compiler
 */
class Properties_Compiler
{

	const DEBUG = true;

	//--------------------------------------------------------------------------------------- $buffer
	/**
	 * @var string
	 */
	private $buffer;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	private $class_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 * @param $buffer     string
	 */
	public function __construct($class_name, &$buffer)
	{
		$this->buffer =& $buffer;
		$this->class_name = $class_name;
		$this->compileStart();
	}

	//---------------------------------------------------------------------------------- compileStart
	/**
	 * Start the compilation process : prepare methods
	 */
	private function compileStart()
	{

	}

	//----------------------------------------------------------------------------- compileProperties
	/**
	 * @param $property_name string
	 * @param $advices       array
	 */
	public function compileProperty($property_name, $advices)
	{
		$class_name = $this->class_name;

		if (self::DEBUG) echo "<h3>Property $class_name::$property_name</h3>";
	}

	//---------------------------------------------------------------------------- getCompiledMethods
	/**
	 * Assembly and return of the compiled methods list
	 *
	 * @return string[] key is the name of the method, value is its code
	 */
	public function getCompiledMethods()
	{
		return array();
	}

}
