<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Sql\Value;

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
		[$table, $field1, $field2, $id1, $id2] = Map::sqlElementsOf(
			$object, $this->property, $foreign_object
		);
		if ($this->property->getType()->getElementTypeAsString() == 'object') {
			$class_field = substr($field2, 3) . '_class';
			return 'INSERT INTO' . SP . BQ . $table . BQ . LF
				. 'SET ' . BQ . $field1 . BQ . ' = ' . $id1 . ', '
				. BQ . $field2 . BQ . ' = ' . $id2 . ', '
				. BQ . $class_field . BQ . ' = ' . Value::escape(get_class($foreign_object));
		}
		return 'INSERT INTO' . SP . BQ . $table . BQ . LF
			. 'SET ' . BQ . $field1 . BQ . ' = ' . $id1 . ', '
			. BQ . $field2 . BQ . ' = ' . $id2;
	}

}
