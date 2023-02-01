<?php
namespace ITRocks\Framework\Reflection;

use ITRocks\Framework\Reflection\Attribute\Has_Attributes;
use ITRocks\Framework\Reflection\Interfaces\Reflection;

abstract class Attribute
{

	//------------------------------------------------------------------------------------ __toString
	abstract public function __toString() : string;

	//---------------------------------------------------------------------------------------- equals
	public static function equals(Reflection $reflection, Reflection $reflection2)
		: bool
	{
		return !strcmp(static::of($reflection), static::of($reflection2));
	}

	//-------------------------------------------------------------------------------------------- of
	/**
	 * @param $reflection Reflection|Has_Attributes
	 * @return static|static[]
	 */
	public static function of(Reflection|Has_Attributes $reflection) : array|object
	{
		return $reflection->getAttribute(get_called_class());
	}

}
