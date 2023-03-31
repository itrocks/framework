<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Is_List;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * A @group annotation contains a name and several values, and is a multiple annotation too
 * It enable to group properties into named groups
 *
 * @example @group first group property_1 property_2 property_3
 * and then @group second group property_4 property_5
 * will create two annotations : one with the name 'first group' and each property name as values,
 * the second with the name 'second group' and each of its property name as string values.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS), Inheritable]
class Group extends Class_
{
	use Is_List { __construct as private parentConstruct; }

	//----------------------------------------------------------------------- Special group CONSTANTS
	public const BOTTOM = '_bottom';
	public const TOP    = '_top';

	//----------------------------------------------------------------------------------------- $name
	/** The name of the group */
	public string $name;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(string $name, string ...$property_paths)
	{
		$this->parentConstruct(...$property_paths);
		$this->name = $name;
	}

	//----------------------------------------------------------------------------------- searchGroup
	/**
	 * Search the $group annotation object matching $name
	 *
	 * @param $groups static[]
	 * @param $name   string
	 * @return ?static
	 */
	public static function searchGroup(array $groups, string $name) : ?static
	{
		foreach ($groups as $group) {
			if ($group->name === $name) {
				return $group;
			}
		}
		return null;
	}

	//-------------------------------------------------------------------------------- searchProperty
	/**
	 * Search the @group annotation object where the property is stored into
	 *
	 * @param $groups   static[]
	 * @param $property Reflection_Property|string
	 * @return ?static
	 */
	public static function searchProperty(array $groups, Reflection_Property|string $property)
		: ?static
	{
		$property_name = is_object($property) ? $property->getName() : $property;
		foreach ($groups as $group) {
			if ($group->has($property_name)) {
				return $group;
			}
		}
		return null;
	}

}
