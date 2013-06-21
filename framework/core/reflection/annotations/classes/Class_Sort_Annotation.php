<?php
namespace SAF\Framework;

/**
 * The sort annotation for classes stores a list of column names for object collections sort
 *
 * This is used by Dao to get default sort orders when calling Dao::readAll() and Dao::search().
 * This work like Class_Representative_Annotation : default values are the complete properties list
 */
class Class_Sort_Annotation extends List_Annotation
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
			$representative = (new Class_Representative_Annotation($value, $class))->value;
			foreach ($class->getAllProperties() as $property) {
				if (in_array($property->name, $representative)) {
					if (!$property->isStatic() && !$property->getAnnotation("link")->value) {
						$this->value[] = $property->name;
					}
				}
			}
		}
	}

}
