<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation\Template;
use ITRocks\Framework\Reflection\Annotation\Template\Multiple_Annotation;
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
class Group_Annotation extends Template\List_Annotation implements Multiple_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'group';

	//----------------------------------------------------------------------------------------- $name
	/**
	 * The group name
	 *
	 * @var string
	 */
	public string $name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value ?string
	 */
	public function __construct(?string $value)
	{
		$value = strval($value);
		$i     = strpos($value, ',');
		if ($i === false) {
			$i = strlen($value);
		}
		$i = strrpos(substr($value, 0, $i), SP);
		if ($i === false) {
			$i = strlen($value);
		}
		$this->name = trim(substr($value, 0, $i));
		parent::__construct(substr($value, $i + 1));
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
	 * @param $property string|Reflection_Property
	 * @return ?static
	 */
	public static function searchProperty(
		array $groups, string|Reflection_Property $property
	) : ?static
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
