<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Tells in which group the property is stored
 *
 * Is no annotation at property level, the class groups are scanned to found which one contains
 * the property.
 *
 * @see Class_\Group
 */
class Group_Annotation extends Annotation implements Property_Context_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'group';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    ?string
	 * @param $property Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct(?string $value, Reflection_Property $property)
	{
		parent::__construct($value);
		if (is_null($this->value)) {
			$group = Class_\Group::searchProperty(
				Class_\Group::of($property->getFinalClass()), $property->getName()
			);
			if ($group) {
				$this->value = $group->name;
			}
		}
	}

	//-------------------------------------------------------------------------------- replaceByClass
	/**
	 * Replace the #Group attribute value by the one set into $class's @class with this property path
	 *
	 * @param $class         Reflection_Class
	 * @param $property_path string
	 */
	public function replaceByClass(Reflection_Class $class, string $property_path) : void
	{
		$class_group_annotation = Class_\Group::searchProperty(
			Class_\Group::of($class), $property_path
		);
		$this->value = $class_group_annotation ? $class_group_annotation->name : false;
	}

}
