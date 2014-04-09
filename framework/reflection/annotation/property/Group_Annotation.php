<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Annotation\Class_\Class_Group_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use SAF\Framework\Reflection\Reflection_Property;

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

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string
	 * @param $property Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct($value, Reflection_Property $property)
	{
		parent::__construct($value);
		if (empty($this->value)) {
			/** @var $group Class_Group_Annotation */
			foreach ($property->getFinalClass()->getAnnotations('group') as $group) {
				if ($group->has($property->name)) {
					$this->value = $property->name;
					break;
				}
			}
		}
	}

}
