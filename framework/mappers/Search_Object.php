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
		foreach (Application::getNamespaces() as $namespace) {
			$class = $namespace . "\\" . $class_name;
			if (@class_exists($class)) {
				$object = new $class();
				break;
			}
		}
		foreach (Class_Fields::accessFields($class_name) as $field) {
			$field_name = $field->name;
			unset($object->$field_name);
		}
		Class_Fields::accessFieldsDone($class_name);
		return $object;
	}

}
