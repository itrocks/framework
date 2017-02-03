<?php
namespace ITRocks\Framework\Mapper;

use ITRocks\Framework\AOP\Joinpoint\Read_Property;

/**
 * Mappers for @var string[] with @values
 */
abstract class String_Array
{

	//---------------------------------------------------------------------------------------- getter
	/**
	 * Use this with @getter String_Array::getter each time you use @var string[] with @values
	 * This patch is because when you read a SET from a mysql database, MySQL (and then it.rocks)
	 * returns a string with 'value1,value2' instead of ['value1', 'value2'].
	 *
	 * @param $joinpoint     Read_Property
	 * @param $object        object
	 * @param $property_name string
	 */
	public static function getter(Read_Property $joinpoint, $object, $property_name)
	{
		if (!is_array($object->$property_name)) {
			$object->$property_name = explode(',', $object->$property_name);
		}
		$joinpoint->disable = true;
		return $object->$property_name;
	}

}
