<?php
namespace ITRocks\Framework\Reflection;

use ITRocks\Framework\Reflection\Attribute\Has_Attributes;
use ITRocks\Framework\Reflection\Interfaces\Reflection;

trait Attribute
{

	//---------------------------------------------------------------------------------------- equals
	public static function equals(Reflection $reflection_object, Reflection $other_reflection_object)
		: bool
	{
		return (static::of($reflection_object)->value === static::of($other_reflection_object)->value);
	}

	//-------------------------------------------------------------------------------------------- of
	/**
	 * @param $has_attributes Reflection|Has_Attributes
	 * @return static|static[]
	 */
	public static function of(Reflection|Has_Attributes $has_attributes) : array|object
	{
		return $has_attributes->getAttribute(get_called_class());
	}

}
