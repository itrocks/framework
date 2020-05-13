<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation\Property;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template;
use ITRocks\Framework\Reflection\Annotation\Template\Class_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * The sort annotation for classes stores a list of column names for object collections sort
 *
 * This is used by Dao to get default sort orders when calling Dao::readAll() and Dao::search().
 * This work like Class_Representative_Annotation : default values are the complete properties list
 */
class Sort_Annotation extends Template\List_Annotation implements Class_Context_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'sort';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Builds representative annotation content
	 *
	 * Default representative is the list of non-static properties of the class
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $value string
	 * @param $class Reflection_Class
	 */
	public function __construct($value, Reflection_Class $class)
	{
		parent::__construct($value);
		// default sort : all representative values but links
		if (!$this->value) {
			$representative = Representative_Annotation::of($class)->value;
			foreach ($representative as $property_path) {
				/** @noinspection PhpUnhandledExceptionInspection class and property must be valid */
				$property = new Reflection_Property($class->getName(), $property_path);
				if (
					!$property->isStatic()
					&& (
						!Property\Link_Annotation::of($property)->value
						|| (
							($store = Store_Annotation::of($property)->value)
							&& ($store !== Store_Annotation::FALSE)
						)
					)
				) {
					$this->value[] = $property_path;
				}
			}
		}
	}

}
