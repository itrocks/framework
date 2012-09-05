<?php
namespace SAF\Framework;

class Sql_Join
{

	const INNER = "INNER";
	const LEFT  = "LEFT";
	const OUTER = "OUTER";
	const RIGHT = "RIGHT";

	const OBJECT = "OBJECT";
	const SIMPLE = "SIMPLE";

	/**
	 * @var string
	 */
	public $foreign_alias;

	/**
	 * @var string
	 */
	public $foreign_field;

	/**
	 * @var string
	 */
	public $foreign_table;

	/**
	 * @var string
	 */
	public $master_alias;

	/**
	 * @var string
	 */
	public $master_field;

	/**
	 * @var string join mode (Sql_Join::INNER, LEFT, OUTER or RIGHT)
	 * @values INNER, LEFT, OUTER, RIGHT
	 */
	public $mode;

	/**
	 * @var join field type (Sql_Join::SIMPLE, OBJECT)
	 * @values SIMPLE, OBJECT
	 */
	public $type = Sql_Join::SIMPLE;

	//-------------------------------------------------------------------------------------- toString
	public function __toString()
	{
		return $this->mode
			. " $this->master_alias.$this->master_field"
			. " = $this->foreign_table $this->foreign_alias.$this->foreign_field"
			. " ($this->type)";
	}

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * @param integer $mode
	 * @param string  $master_alias
	 * @param string  $master_field
	 * @param string  $foreign_table
	 * @param string  $foreign_alias
	 * @param string  $foreign_field
	 * @return Sql_Join
	 */
	public static function newInstance(
		$mode, $master_alias, $master_field, $foreign_alias, $foreign_table, $foreign_field,
		$type = Sql_Join::SIMPLE
	) {
		$sql_join = new Sql_Join();
		$sql_join->foreign_alias = $foreign_alias;
		$sql_join->foreign_field = $foreign_field;
		$sql_join->foreign_table = $foreign_table;
		$sql_join->master_alias  = $master_alias;
		$sql_join->master_field  = $master_field;
		$sql_join->mode          = $mode;
		$sql_join->type          = $type;
		return $sql_join;
	}

}
