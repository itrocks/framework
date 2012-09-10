<?php
namespace SAF\Framework;

abstract class Sql_Table
{

	//------------------------------------------------------------------------------ classToTableName
	/**
	 * Convert a full class name or a reflection class to a SQL table name 
	 *
	 * @param ReflectionClass | string $class
	 * @return string
	 */
	public static function classToTableName($class)
	{
		if (!$class instanceof Reflection_Class) {
			$class = Reflection_Class::getInstanceOf($class);
		}
		$dataset = $class->getDataset();
		if ($dataset) {
			return strtolower($dataset);
		}
		else {
			$class_name = Namespaces::shortClassName($class->name);
			return (substr($class_name, -1) === "y")
				? (strtolower(substr($class_name, 0, -1)) . "ies")
				: (strtolower($class_name) . "s");
		}
	}

}
