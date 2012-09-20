<?php
namespace SAF\Framework;

abstract class Sql_Table
{

	//------------------------------------------------------------------------------ classToTableName
	/**
	 * Convert a full class name or a reflection class to a SQL table name 
	 *
	 * @param  string $class_name
	 * @return string
	 */
	public static function classToTableName($class_name)
	{
		return Dao::current()->storeNameOf($class_name);
		if ($table_name) {
			return $table_name;
		}
		else {
			$dataset = Reflection_Class::getInstanceOf($class_name)->getDataSet();
			if ($dataset) {
				return strtolower($dataset);
			}
			else {
				$class_name = Namespaces::shortClassName($class_name);
				return (substr($class_name, -1) === "y")
					? (strtolower(substr($class_name, 0, -1)) . "ies")
					: (strtolower($class_name) . "s");
			}
		}
	}

}
