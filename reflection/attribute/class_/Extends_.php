<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Attribute\Has_Attributes;
use ITRocks\Framework\Reflection\Interfaces;

/**
 * This must be used for traits that are designed to extend a given class
 * Builder will use it to sort built classes
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class Extends_ extends Reflection\Attribute
{

	//---------------------------------------------------------------------------------------- STRICT
	/**
	 * strict option : if set, this attribute is used for Builder's extends information only, not
	 * for trait installation automatic feature build application
	 */
	public const STRICT = 'strict';

	//-------------------------------------------------------------------------------------- $extends
	/**
	 * @var class-string[]
	 */
	public array $extends = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $extends class-string[]
	 */
	public function __construct(string... $extends)
	{
		$this->extends = $extends;
		foreach ($this->extends as &$extends) {
			$extends = Builder::className($extends);
		}
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		sort($this->extends);
		return join(LF, $this->extends);
	}

	//------------------------------------------------------------------------------------------- has
	/**
	 * @param $class_name class-string
	 * @return boolean
	 */
	public function has(string $class_name) : bool
	{
		return in_array($class_name, $this->extends);
	}

	//----------------------------------------------------------------------------------------- notOf
	/**
	 * Get all attributes of a reflection object
	 *
	 * @param $reflection Interfaces\Reflection|Has_Attributes
	 * @param $filter     class-string filter to get only extends not containing this class
	 * @return static[]
	 */
	public static function notOf(Interfaces\Reflection|Has_Attributes $reflection, string $filter)
		: array
	{
		$attributes = static::of($reflection);
		return array_filter(
			$attributes, function($attribute) use($filter) { return !$attribute->has($filter); }
		);
	}

	//-------------------------------------------------------------------------------------------- of
	/**
	 * Get all attributes of a reflection object
	 *
	 * @param $reflection Interfaces\Reflection|Has_Attributes
	 * @param $filter     class-string filter to get only extends containing this class
	 * @return static[]
	 */
	public static function of(Interfaces\Reflection|Has_Attributes $reflection, string $filter = '')
		: array
	{
		$attributes = parent::of($reflection);
		if (!$attributes || !$filter) {
			return $attributes;
		}
		return array_filter(
			$attributes, function($attribute) use($filter) { return $attribute->has($filter); }
		);
	}

	//-------------------------------------------------------------------------------------- oneNotOf
	/**
	 * Get the first attribute of a reflection object matching filter (if set).
	 * If none, this creates an empty attribute.
	 *
	 * @param $reflection Interfaces\Reflection|Has_Attributes
	 * @param $filter     class-string filter to get only extends containing this class
	 * @return static
	 */
	public static function oneNotOf(
		Interfaces\Reflection|Has_Attributes $reflection, string $filter = ''
	) : static
	{
		$attributes = static::notOf($reflection, $filter);
		return $attributes
			? reset($attributes)
			: new static();
	}

	//----------------------------------------------------------------------------------------- oneOf
	/**
	 * Get the first attribute of a reflection object matching filter (if set).
	 * If none, this creates an empty attribute.
	 *
	 * @param $reflection Interfaces\Reflection|Has_Attributes
	 * @param $filter     class-string filter to get only extends containing this class
	 * @return static
	 */
	public static function oneOf(
		Interfaces\Reflection|Has_Attributes $reflection, string $filter = ''
	) : static
	{
		$attributes = static::of($reflection, $filter);
		return $attributes
			? reset($attributes)
			: new static();
	}

}
