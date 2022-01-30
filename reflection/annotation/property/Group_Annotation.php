<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Tells in which group the property is stored
 *
 * Is no annotation at property level, the class groups are scanned to found which one contains
 * the property.
 *
 * @see Class_Group_Annotation
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
			$group = Class_\Group_Annotation::searchProperty(
				Class_\Group_Annotation::allOf($property->getFinalClass()), $property->getName()
			);
			if ($group) {
				$this->value = $group->name;
			}
		}
	}

	//-------------------------------------------------------------------------------- replaceByClass
	/**
	 * Replace the @group annotation value by the one set into $class's @class with this property path
	 *
	 * @param $class         Reflection_Class
	 * @param $property_path string
	 */
	public function replaceByClass(Reflection_Class $class, string $property_path)
	{
		$class_group_annotation = Class_\Group_Annotation::searchProperty(
			Class_\Group_Annotation::allOf($class), $property_path
		);
		$this->value = $class_group_annotation ? $class_group_annotation->name : false;
	}

}
