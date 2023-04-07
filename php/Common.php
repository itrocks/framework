<?php
namespace ITRocks\Framework\PHP;

use ITRocks\Framework\Reflection\Attribute\Class_\Implement;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Interfaces\Reflection;

#[Implement(Reflection::class)]
trait Common
{

	//--------------------------------------------------------------------------- getAttributesCommon
	/**
	 * Gets the attributes list associated to the element
	 *
	 * _INHERITABLE attributes : parent (and interface and class) attributes are scanned too.
	 *
	 * The returned array key is the name of the attribute.
	 *
	 * The value of each returned array element is :
	 * - !Attribute::IS_REPEATABLE attributes : a single ReflectionAttribute.
	 * - Attribute::IS_REPEATABLE attributes : an array of ReflectionAttribute.
	 *
	 * @return Reflection_Attribute[]|Reflection_Attribute[][]
	 */
	public function getAttributesCommon(
		string $name = null, int $flags = 0, Interfaces\Reflection $final = null,
		Interfaces\Reflection_Class $class = null
	) : array
	{
		$attributes = [];
		/** @noinspection PhpMultipleClassDeclarationsInspection All parents use Has_Attributes */
		foreach ($this->attributes as $attribute) {
			if ($name && ($attribute->getName() !== $name)) continue;
			$attribute->setFinalDeclaring($final, $class);
			if ($this->isAttributeRepeatable($attribute->getName())) {
				$attributes[$attribute->getName()][] = $attribute;
			}
			else {
				$attributes[$attribute->getName()] = $attribute;
			}
		}
		return $attributes;
	}

	//--------------------------------------------------------------------------------- newReflection
	public static function newReflection(string $class, string $member = null) : Reflection
	{
		if (!$member) {
			return Reflection_Class::of($class);
		}
		if (str_starts_with($member, '$')) {
			return Reflection_Property::of($class, $member);
		}
		return Reflection_Method::of($class, $member);
	}

	//---------------------------------------------------------------------------- newReflectionClass
	public static function newReflectionClass(string $class) : Reflection_Class
	{
		return Reflection_Class::of($class);
	}

	//--------------------------------------------------------------------------- newReflectionMethod
	public static function newReflectionMethod(string $class, string $method) : Reflection_Method
	{
		return Reflection_Method::of($class, $method);
	}

	//------------------------------------------------------------------------- newReflectionProperty
	public static function newReflectionProperty(string $class, string $property)
		: Reflection_Property
	{
		return Reflection_Property::of($class, $property);
	}

}
