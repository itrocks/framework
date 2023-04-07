<?php
namespace ITRocks\Framework\Reflection\Attribute;

use ITRocks\Framework\Reflection\Interfaces\Reflection;

trait Common
{

	//------------------------------------------------------------------------------------ __toString
	abstract public function __toString() : string;

	//---------------------------------------------------------------------------------------- equals
	public static function equals(Reflection $reflection, Reflection $reflection2) : bool
	{
		return !strcmp(static::of($reflection), static::of($reflection2));
	}

	//-------------------------------------------------------------------------------------------- of
	/** @return static|static[]|null */
	public static function of(Reflection|Has_Attributes $reflection) : array|object|null
	{
		return $reflection->getAttribute(static::class);
	}

}
