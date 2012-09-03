<?php
namespace Framework;

abstract class Search_Object
{

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * @param string $class_name
	 * @return object
	 */
	public static function newInstance($class_name)
	{
		$object = new $class_name();
		foreach (Class_Fields::accessFields($class_name) as $field) {
			$field_name = $field->name;
			unset($object->$field_name);
		}
		Class_Fields::accessFieldsDone($class_name);
		return $object;
	}

}
