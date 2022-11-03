<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Sql\Link_Table;

/**
 * The SQL map builder builds elements useful for the database representation of an objects map
 */
abstract class Map
{

	//--------------------------------------------------------------------------------- sqlElementsOf
	/**
	 * Gets sql elements for a mapping between two objects
	 *
	 * @example
	 * [$table, $field1, $field2, $id1, $id2] = Sql_Map_Builder::sqlElementsOf( (...) );
	 * @param $object         object the source object
	 * @param $property       Reflection_Property the property of the source object used for the mapping
	 * @param $foreign_object object the mapped object
	 * @return array [string $table, string $master_column, string $foreign_column,
	 *               integer $id_object, integer $id_foreign_object]
	 */
	public static function sqlElementsOf(
		object $object, Reflection_Property $property, object $foreign_object
	) : array
	{
		// build table and fields
		$sql_link = new Link_Table($property);
		$table    = $sql_link->table();
		$field1   = $sql_link->masterColumn();
		$field2   = $sql_link->foreignColumn();
		// build values
		$id1 = Dao::getObjectIdentifier($object, 'id');
		$id2 = Dao::getObjectIdentifier($foreign_object, 'id');
		// return elements
		return [$table, $field1, $field2, $id1, $id2];
	}

}
