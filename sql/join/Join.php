<?php
namespace ITRocks\Framework\Sql;

use ITRocks\Framework;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * This stores data for SQL joins and enable to output SQL expression for a table join
 */
class Join
{

	//------------------------------------------------------------------------------------- JOIN MODE
	const INNER = 'INNER';
	const LEFT  = 'LEFT';
	const OUTER = 'OUTER';
	const RIGHT = 'RIGHT';

	//------------------------------------------------------------------------------------- JOIN TYPE
	const LINK   = 'LINK';
	const OBJECT = 'OBJECT';
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

	//----------------------------------------------------------------------------------------- $like
	/**
	 * @var boolean[] key 0 : primary join, keys 1..n : secondary joins
	 */
	public $like = [];

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
	 * @values INNER, LEFT, OUTER, RIGHT
	 * @var string
	 */
	public $mode;

	//------------------------------------------------------------------------------------ $secondary
	/**
	 * Secondary conditions for the join
	 *
	 * Key is the foreign column name, value if 'constant' / "constant" / master column name
	 *
	 * @var string[]
	 */
	public $secondary = [];

	//----------------------------------------------------------------------------------------- $type
	/**
	 * Join column type (Sql_Join::SIMPLE, OBJECT)
	 *
	 * @values SIMPLE, OBJECT
	 * @var string
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

	//------------------------------------------------------------------------------------ foreignSql
	/**
	 * @return string
	 */
	public function foreignSql()
	{
		return $this->foreign_alias . DOT . BQ . $this->foreign_column . BQ;
	}

	//------------------------------------------------------------------------------------- masterSql
	/**
	 * @return string
	 */
	public function masterSql()
	{
		return $this->master_alias . DOT . BQ . $this->master_column . BQ;
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
		$sign = ($this->like[0] ?? false) ? 'LIKE' : '=';
		$sql = LF . $this->mode . ' JOIN ' . BQ . $this->foreign_table . BQ . SP . $this->foreign_alias
			. ' ON ' . $this->foreignSql() . SP . $sign . SP . $this->masterSql();
		foreach ($this->secondary as $foreign => $master) {
			$sign = ($this->like[$foreign] ?? false) ? 'LIKE' : '=';
			$sql .= ' AND ' . $this->foreign_alias . DOT . BQ . $foreign . BQ;
			$sql .= in_array($master[0], [DQ, Q])
				? (SP . $sign . SP . $master)
				: (SP . $sign . SP . $this->master_alias . DOT . BQ . $master . BQ);
		}
		return $sql;
	}

}
