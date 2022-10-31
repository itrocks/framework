<?php
namespace ITRocks\Framework\Dao\Option;

use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Reflection\Annotation\Class_\Link_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Link_Class;

/**
 * Set this option to write link class data only.
 *
 * For an object which class has a @link annotation : only properties data from the link class
 * are written.
 *
 * For an object which class has no @link annotation : all proporties are written.
 *
 * This is used internally by Data_Links to avoid writing linked class data of link objects
 * collection and map.
 *
 * Developers can use this for their particular cases.
 */
class Link_Class_Only implements Option
{

	//---------------------------------------------------------------------------------- propertiesOf
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class Reflection_Class|string
	 * @return Reflection_Property[] link-class-only properties. empty array if not a Link_Class.
	 */
	public static function propertiesOf(Reflection_Class|string $class) : array
	{
		if (!is_a($class, Link_Class::class)) {
			/** @noinspection PhpUnhandledExceptionInspection class must be valid */
			$class = new Link_Class($class);
		}
		return Link_Annotation::of($class)->value ? $class->getLinkProperties() : [];
	}

}
