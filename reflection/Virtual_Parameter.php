<?php
namespace ITRocks\Framework\Reflection;

/**
 * This is a virtual Reflection_Parameter object, needed for internal methods optional parameters
 * that could not be read with ReflectionMethod::getParameters()
 *
 * @example mysqli::query() need it for its secondary parameter $resultmode
 */
class Virtual_Parameter
{

	//-------------------------------------------------------------------------------------- $default
	/**
	 * @var mixed
	 */
	private mixed $default;

	//------------------------------------------------------------------------------------- $function
	/**
	 * @var string|string[]
	 */
	private array|string $function;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public string $name;

	//------------------------------------------------------------------------------------- $optional
	/**
	 * @var boolean
	 */
	private bool $optional;

	//------------------------------------------------------------------------------------ $reference
	/**
	 * @var boolean
	 */
	private bool $reference;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $function          string|string[] function name or ["Class_Name", "methodName")
	 * @param $parameter_name    string parameter name
	 * @param $optional          boolean is the parameter optional
	 * @param $default           mixed default value for the optional parameter
	 * @param $pass_by_reference boolean
	 */
	public function __construct(
		array|string $function, string $parameter_name, bool $optional = false, mixed $default = null,
		bool $pass_by_reference = false
	) {
		$this->function  = $function;
		$this->name      = $parameter_name;
		$this->optional  = $optional;
		$this->default   = $default;
		$this->reference = $pass_by_reference;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		$default = $this->default;
		return ($this->reference ? '&' : '') . '$' . $this->name
		. ($this->optional
			? (' = ' . (is_numeric($default) ? $default : (DQ . addslashes($default). DQ)))
			: ''
		);
	}

}
