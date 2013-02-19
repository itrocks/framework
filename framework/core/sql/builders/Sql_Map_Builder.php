<?php
namespace SAF\Framework;

abstract class Sql_Map_Builder
{

	//--------------------------------------------------------------------------------- sqlElementsOf
	/**
	 * Gets sql elements for a mapping between two objects
	 *
	 * @example
	 * list($table, $field1, $field2, $id1, $id2) = Sql_Map_Builder::sqlElementsOf( (...) );
	 *
	 * @param $object         object the source object
	 * @param $property       Reflection_Property the property of the source object used for the mapping
	 * @param $foreign_object object the mapped object
	 * @return array
	 */
	public static function sqlElementsOf($object, $property, $foreign_object)
	{
		// build table name
		$table1 = Dao::storeNameOf(get_class($object));
		$table2 = Dao::storeNameOf(get_class($foreign_object));
		$table = ($table1 < $table2)
			? ($table1 . "_" . $table2 . "_links")
			: ($table2 . "_" . $table1 . "_links");
		// build fields names
		$field1 = "id_" . $property->getAnnotation("foreign")->value;
		$field2 = "id_" . $property->getAnnotation("foreignlink")->value;
		// build values
		$id1 = Dao::getObjectIdentifier($object);
		$id2 = Dao::getObjectIdentifier($foreign_object);
		// return elements
		return array($table, $field1, $field2, $id1, $id2);
	}

}
