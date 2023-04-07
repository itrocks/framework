<?php
namespace ITRocks\Framework\Reflection\Attribute\Template;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Interfaces\Reflection;

/**
 * Constant or callable attribute
 *
 * The value may be a callable method, or a constant value (text)
 */
trait Has_Constant_Or_Callable
{
	use Has_Callable {
		__construct as private callableConstruct;
		__toString  as private callableToString;
		call        as private callableCall;
		setFinal    as private callableSetFinal;
	}

	//------------------------------------------------------------------------------------- $constant
	public mixed $constant;

	//---------------------------------------------------------------------------------- $is_constant
	public bool $is_constant;

	//----------------------------------------------------------------------------------- __construct
	/** @param $value callable|mixed Constant or callable */
	public function __construct(mixed $value = self::AUTO)
	{
		if (
			($value === self::AUTO)
			|| (
				is_array($value)
				&& (count($value) === 2)
				&& (is_object($value[0]) || is_string($value[0] ?? 0))
				&& is_string($value[1] ?? 0)
			)
		) {
			$this->is_constant = false;
			$this->callableConstruct($value);
			return;
		}
		$this->constant    = $value;
		$this->is_constant = true;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->is_constant ? strval($this->constant) : $this->callableToString();
	}

	//------------------------------------------------------------------------------------------ call
	/**
	 * @param $object    object|string|null the object will be the first. string = a class name
	 * @param $arguments array
	 * @return mixed the value returned by the called method
	 */
	public function call(object|string|null $object, array $arguments = []) : mixed
	{
		return $this->is_constant
			? ((is_string($this->constant) && $this->constant) ? Loc::tr($this->constant) : '')
			: $this->callableCall($object, $arguments);
	}

	//-------------------------------------------------------------------------------------- setFinal
	public function setFinal(Reflection $reflection) : void
	{
		if ($this->is_constant) {
			return;
		}
		$this->callableSetFinal($reflection);
	}

}
