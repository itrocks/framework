<?php
namespace SAF\Framework\Sql\Builder;

use SAF\Framework\Reflection\Reflection_Property;

/**
 * SQL insert queries builder for a mapped object
 */
class Map_Insert
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	private $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property Reflection_Property
	 */
	public function __construct(Reflection_Property $property)
	{
		$this->property = $property;
	}

	//------------------------------------------------------------------------------------ buildQuery
	/**
	 * @param $object         object
	 * @param $foreign_object object
	 * @return string
	 */
	public function buildQuery($object, $foreign_object)
	{
		list($table, $field1, $field2, $id1, $id2) = Map::sqlElementsOf(
			$object, $this->property, $foreign_object
		);
		return 'INSERT INTO ' . BQ . $table . BQ . ' (' . BQ . $field1 . BQ . ', ' . BQ . $field2 . BQ . ')'
			. ' VALUES (' . $id1 . ', ' . $id2 . ')';
	}

}
