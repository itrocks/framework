<?php
namespace SAF\Framework;

/**
 * Builds Mysql_Table object with a structure matching the structure of a PHP class
 */
abstract class Mysql_Table_Builder_Class
{

	//----------------------------------------------------------------------------------------- build
	/**
	 * Builds a Mysql_Table object using a Php class definition
	 *
	 * A Php class becomes a Mysql_Table
	 * Non-static properties of the class will become Mysql_Column objects
	 *
	 * @param $class_name string
	 * @return Mysql_Table
	 */
	public static function build($class_name)
	{
		$class = Reflection_Class::getInstanceOf($class_name);
		$table_name = Dao::current()->storeNameOf($class_name);
		$table = new Mysql_Table($table_name);
		$table->columns["id"] = Mysql_Column_Builder::buildId();
		foreach ($class->accessProperties() as $property) {
			$type = $property->getType();
			if (($type->isMultipleString() || !$type->isMultiple()) && !$property->isStatic()) {
				$column = Mysql_Column_Builder_Property::build($property);
				$table->columns[$column->getName()] = $column;
			}
		}
		$class->accessPropertiesDone();
		return $table;
	}

}
