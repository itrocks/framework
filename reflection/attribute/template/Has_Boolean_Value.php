<?php
namespace ITRocks\Framework\Reflection\Attribute\Template;

use ITRocks\Framework\Reflection\Attribute\Class_\Implement;
use ITRocks\Framework\Reflection\Interfaces\Reflection;

#[Implement(Has_Get_Default_Arguments::class)]
trait Has_Boolean_Value
{

	//---------------------------------------------------------------------------------------- $value
	public bool $value;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(bool $value = true)
	{
		$this->value = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->value ? '1' : '0';
	}

	//--------------------------------------------------------------------------- getDefaultArguments
	public static function getDefaultArguments(Reflection $reflection) : array
	{
		return [false];
	}

}
