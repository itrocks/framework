<?php
namespace ITRocks\Framework\Reflection;

use ITRocks\Framework\Reflection\Attribute\Class_\Implement;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ReflectionException;

#[Implement(Reflection::class)]
trait Common
{

	//--------------------------------------------------------------------------------- newReflection
	/** @throws ReflectionException */
	public static function newReflection(string $class, string $member = null) : Reflection
	{
		if (!$member) {
			return new Reflection_Class($class);
		}
		if (str_starts_with($member, '$')) {
			return new Reflection_Property($class, $member);
		}
		return new Reflection_Method($class, $member);
	}

	//---------------------------------------------------------------------------- newReflectionClass
	/** @throws ReflectionException */
	public static function newReflectionClass(string $class) : Reflection_Class
	{
		return new Reflection_Class($class);
	}

	//--------------------------------------------------------------------------- newReflectionMethod
	/** @throws ReflectionException */
	public static function newReflectionMethod(string $class, string $method) : Reflection_Method
	{
		return new Reflection_Method($class, $method);
	}

	//------------------------------------------------------------------------- newReflectionProperty
	/** @throws ReflectionException */
	public static function newReflectionProperty(string $class, string $property)
		: Reflection_Property
	{
		return new Reflection_Property($class, $property);
	}

}
