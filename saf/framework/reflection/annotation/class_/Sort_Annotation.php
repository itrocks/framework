<?php
namespace SAF\Framework\Reflection\Annotation\Class_;

use SAF\Framework\Reflection\Annotation\Template\Class_Context_Annotation;
use SAF\Framework\Reflection\Annotation\Template\List_Annotation;
use SAF\Framework\Reflection\Interfaces\Reflection_Class;

/**
 * The sort annotation for classes stores a list of column names for object collections sort
 *
 * This is used by Dao to get default sort orders when calling Dao::readAll() and Dao::search().
 * This work like Class_Representative_Annotation : default values are the complete properties list
 */
class Sort_Annotation extends List_Annotation implements Class_Context_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Builds representative annotation content
	 *
	 * Default representative is the list of non-static properties of the class
	 *
	 * @param $value string
	 * @param $class Reflection_Class
	 */
	public function __construct($value, Reflection_Class $class)
	{
		parent::__construct($value);
		// default sort : all representative values but links
		if (!$this->value) {
			$representative = (new Representative_Annotation($value, $class))->value;
			foreach ($class->getProperties([T_EXTENDS, T_USE]) as $property) {
				if (in_array($property->getName(), $representative)) {
					if (!$property->isStatic() && !$property->getAnnotation('link')->value) {
						$this->value[] = $property->getName();
					}
				}
			}
		}
	}

}
