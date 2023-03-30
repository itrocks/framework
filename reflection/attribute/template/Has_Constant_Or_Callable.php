<?php
namespace ITRocks\Framework\Reflection\Attribute\Template;

use ITRocks\Framework\Locale\Loc;

/**
 * Constant or callable attribute
 *
 * The value may be a callable method, or a constant value (text)
 */
trait Has_Constant_Or_Callable
{
	use Has_Callable {
		__construct as private parentConstruct;
		__toString as private parentToString;
		call as private parentCall;
	}

	//------------------------------------------------------------------------------------- $constant
	public mixed $constant;
	
	//----------------------------------------------------------------------------------- __construct
	public function __construct(mixed $value, bool $static = false)
	{
		if (is_array($value) && (($value[0] === self::STATIC) || is_callable($value))) {
			$this->parentConstruct($value, $static);
			return;
		}
		$this->constant = $value;
		$this->static   = $static;
		$this->value    = [];
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->value
			? $this->parentToString()
			: strval($this->constant);
	}

	//------------------------------------------------------------------------------------------ call
	/**
	 * @param $object    object|string|null the object will be the first. string = a class name
	 * @param $arguments array
	 * @return mixed the value returned by the called method
	 */
	public function call(object|string|null $object, array $arguments = []) : mixed
	{
		return $this->value
			? $this->parentCall($object, $arguments)
			: ((is_string($this->constant) && $this->constant) ? Loc::tr($this->constant) : '');
	}

}
