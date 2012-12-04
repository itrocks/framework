<?php
namespace SAF\Framework;

abstract class Mysql_Table_Builder_Class
{

	//----------------------------------------------------------------------------------------- build
	public static function build($class_name)
	{
		$class = Reflection_Class::getInstanceOf($class_name);
		$table_name = Dao::current()->storeNameOf($class_name);
		$table = new Mysql_Table($table_name);
		$mysql_column_class = Reflection_Class::getInstanceOf(__NAMESPACE__ . "\\Mysql_Column");
		$mysql_column_class->accessProperties();
		foreach ($class->accessProperties() as $property) {
			$table->columns[] = Mysql_Column_Builder_Property::build($property);
		}
		$mysql_column_class->accessPropertiesDone();
		$class->accessPropertiesDone();
	}

}
