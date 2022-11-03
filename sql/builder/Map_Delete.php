<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * SQL delete queries builder for a mapped object
 */
class Map_Delete
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	private Reflection_Property $property;

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
	public function buildQuery(object $object, object $foreign_object) : string
	{
		[$table, $field1, $field2, $id1, $id2] = Map::sqlElementsOf(
			$object, $this->property, $foreign_object
		);
		return 'DELETE FROM' . SP . BQ . $table . BQ . LF
			. 'WHERE ' . BQ . $field1 . BQ . ' = ' . $id1 . ' AND ' . BQ . $field2 . BQ . ' = ' . $id2;
	}

}
