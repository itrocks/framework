<?php
namespace SAF\Framework;

class Sql_Map_Delete_Builder
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
		list($table, $field1, $field2, $id1, $id2) = Sql_Map_Builder::sqlElementsOf(
			$object, $this->property, $foreign_object
		);
		return "DELETE FROM `" . $table . "`"
			. " WHERE `" . $field1 . "` = " . $id1 . " AND `" . $field2 . "` = " . $id2;
	}

}
