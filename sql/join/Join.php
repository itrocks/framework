<?php
namespace SAF\Framework\Sql;

use SAF\Framework;
use SAF\Framework\Reflection\Reflection_Property;

/**
 * This stores data for SQL joins and enable to output SQL expression for a table join
 */
class Join
{

	//------------------------------------------------------------------------------------- JOIN MODE

	//----------------------------------------------------------------------------------------- INNER
	/**
	 * INNER JOIN
	 */
	const INNER = 'INNER';

	//------------------------------------------------------------------------------------------ LEFT
	/**
	 * LEFT JOIN
	 */
	const LEFT = 'LEFT';

	//----------------------------------------------------------------------------------------- OUTER
	/**
	 * OUTER JOIN
	 */
	const OUTER = 'OUTER';

	//----------------------------------------------------------------------------------------- RIGHT
	/**
	 * RIGHT JOIN
	 */
	const RIGHT = 'RIGHT';

	//------------------------------------------------------------------------------------- JOIN TYPE

	//------------------------------------------------------------------------------------------ LINK
	/**
	 * A property set here because of a 'link' annotated class
	 */
	const LINK = 'LINK';

	//---------------------------------------------------------------------------------------- OBJECT
	/**
	 * An object property
	 */
	const OBJECT = 'OBJECT';

	//---------------------------------------------------------------------------------------- SIMPLE
	/**
	 * A simple value property
	 */
	const SIMPLE = 'SIMPLE';

	//-------------------------------------------------------------------------------- $foreign_alias
	/**
	 * Alias for foreign table (t1..tn)
	 *
	 * @var string
	 */
	public $foreign_alias;

	//-------------------------------------------------------------------------------- $foreign_class
	/**
	 * Foreign class name. Can be null if foreign table has no associated class (ie link table)
	 *
	 * @var string
	 */
	public $foreign_class;

	//------------------------------------------------------------------------------- $foreign_column
	/**
	 * Foreign column name
	 *
	 * @var string
	 */
	public $foreign_column;

	//----------------------------------------------------------------------------- $foreign_property
	/**
	 * The property that matches the foreign column (if set)
	 *
	 * @var Reflection_Property
	 */
	public $foreign_property;

	//-------------------------------------------------------------------------------- $foreign_table
	/**
	 * Foreign table name
	 *
	 * @var string
	 */
	public $foreign_table;

	//--------------------------------------------------------------------------------- $linked_class
	/**
	 * Linked class join
	 *
	 * @var Join
	 */
	public $linked_class;

	//---------------------------------------------------------------------------------- $linked_join
	/**
	 * Linked join
	 *
	 * @var Join
	 */
	public $linked_join;

	//--------------------------------------------------------------------------------- $master_alias
	/**
	 * Alias for master table (t0..tn)
	 *
	 * @var string
	 */
	public $master_alias;

	//-------------------------------------------------------------------------------- $master_column
	/**
	 * Master column name
	 *
	 * @var string
	 */
	public $master_column;

	//------------------------------------------------------------------------------ $master_property
	/**
	 * The property that matches the master column (if set)
	 *
	 * @var Reflection_Property
	 */
	public $master_property;

	//----------------------------------------------------------------------------------------- $mode
	/**
	 * Join mode (Sql_Join::INNER, LEFT, OUTER or RIGHT)
	 *
	 * @var string
	 * @values INNER, LEFT, OUTER, RIGHT
	 */
	public $mode;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * Join column type (Sql_Join::SIMPLE, OBJECT)
	 *
	 * @var string
	 * @values SIMPLE, OBJECT
	 */
	public $type = self::SIMPLE;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->toSql() . ' [' . $this->type . ']';
	}

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * Generate a new Sql_Join instance, with initialization of all its properties
	 *
	 * @param $mode           integer
	 * @param $master_alias   string
	 * @param $master_column  string
	 * @param $foreign_table  string
	 * @param $foreign_alias  string
	 * @param $foreign_column string
	 * @param $type           string
	 * @param $foreign_class  string
	 * @return Join
	 */
	public static function newInstance(
		$mode, $master_alias, $master_column, $foreign_alias, $foreign_table, $foreign_column,
		$type = self::SIMPLE, $foreign_class = null
	) {
		$sql_join = new Join();
		$sql_join->foreign_alias  = $foreign_alias;
		$sql_join->foreign_class  = isset($foreign_class)
			? Framework\Builder::className($foreign_class)
			: null;
		$sql_join->foreign_column = $foreign_column;
		$sql_join->foreign_table  = $foreign_table;
		$sql_join->master_alias   = $master_alias;
		$sql_join->master_column  = $master_column;
		$sql_join->mode           = $mode;
		$sql_join->type           = $type;
		return $sql_join;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * @return string
	 */
	public function toSql()
	{
		return LF . $this->mode . ' JOIN ' . BQ . $this->foreign_table . BQ . SP . $this->foreign_alias
		. ' ON ' . $this->foreign_alias . DOT . $this->foreign_column
		. ' = ' . $this->master_alias . DOT . $this->master_column;
	}

}
