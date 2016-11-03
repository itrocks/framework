<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
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

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string
	 * @param $property Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct($value, Reflection_Property $property)
	{
		parent::__construct($value);
		if (empty($this->value)) {
			foreach ($property->getFinalClass()->getAnnotations('group') as $group) {
				/** @var $group Class_\Group_Annotation */
				if ($group->has($property->getName())) {
					$this->value = $property->getName();
					break;
				}
			}
		}
	}

}
