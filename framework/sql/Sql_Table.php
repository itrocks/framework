<?php
namespace Framework;

class Sql_Table
{

	//------------------------------------------------------------------------------ classToTableName
	public static function classToTableName($object_class)
	{
		$class = new Reflection_Class($object_class);
		$annotation = $class->getAnnotation("dataset");
		if ($annotation) {
			return strtolower($annotation->value);
		} else {
			$class_name = $object_class;
			if ($i = strrpos($class_name, "\\")) {
				$class_name = substr($class_name, $i + 1);
			}
			if (substr($class_name, -1) === "y") {
				return strtolower(substr($class_name, 0, -1)) . "ies";
			} else {
				return strtolower($class_name) . "s";
			}
		}
	}

}
