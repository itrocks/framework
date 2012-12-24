<?php
namespace SAF\Framework;

abstract class Mysql_Table_Builder_Class
{

	//----------------------------------------------------------------------------------------- build
	/**
	 * Builds a Mysql_Table object using a Php class definition
	 *
	 * A Php class becomes a Mysql_Table
	 * Non-static properties of the class will become Mysql_Column objects
	 *
	 * @param string $class_name
	 * @return Mysql_Table
	 */
	public static function build($class_name)
	{
		$class = Reflection_Class::getInstanceOf($class_name);
		$table_name = Dao::current()->storeNameOf($class_name);
		$table = new Mysql_Table($table_name);
		$table->columns["id"] = Mysql_Column_Builder_Property::buildId();
		foreach ($class->accessProperties() as $property) {
			$type = $property->getType();
			if ((($type === "multitype:string") || !Type::isMultiple($type)) && !$property->isStatic()) {
				$column = Mysql_Column_Builder_Property::build($property);
				$table->columns[$column->getName()] = $column;
			}
		}
		$class->accessPropertiesDone();
		return $table;
	}

}
