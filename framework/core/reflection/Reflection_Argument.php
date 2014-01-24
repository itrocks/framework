<?php
namespace SAF\Framework;

/**
 * A Reflection_Method argument
 */
class Reflection_Argument
{

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var string
	 */
	public $class;

	//--------------------------------------------------------------------------------------- $method
	/**
	 * @var string
	 */
	public $method;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//------------------------------------------------------------------------------------- $required
	/**
	 * @var boolean
	 */
	public $required;

	//-------------------------------------------------------------------------------------- $default
	/**
	 * @var mixed
	 */
	public $default;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name    string
	 * @param $method_name   string
	 * @param $argument_name string
	 * @param $default       mixed
	 * @param $required      boolean Do never use it : for internal use only
	 */
	public function __construct(
		$class_name, $method_name, $argument_name, $default = null, $required = null
	) {
		$this->class    = $class_name;
		$this->method   = $method_name;
		$this->name     = $argument_name;
		$this->default  = $default;
		$this->required = isset($required)
			? $required
			: (new Reflection_Method($class_name, $method_name))->getArgument($argument_name)->required;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * Return the argument as a PHP source string
	 *
	 * @example "$argument_name"
	 * @example "$argument_name = 'default'"
	 * @example "$argument_name = 10"
	 * @return string
	 */
	public function __toString()
	{
		return '$' . $this->name
		. ($this->required
			? ""
			: (" = " . (is_numeric($this->default) ?: ("'" . $this->default . "'")))
		);
	}

}
