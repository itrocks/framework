<?php
namespace SAF\Framework;

/**
 * This stores data for SQL joins and enable to output SQL expression for a table join
 */
class Sql_Join
{

	//------------------------------------------------------------------------------------- JOIN MODE
	const INNER = 'INNER'; // inner join
	const LEFT  = 'LEFT';  // left join
	const OUTER = 'OUTER'; // outer join
	const RIGHT = 'RIGHT'; // right join

	//------------------------------------------------------------------------------------- JOIN TYPE
	const LINK   = 'LINK';   // a property set here because of a 'link' annotated class
	const OBJECT = 'OBJECT'; // an object property
	const SIMPLE = 'SIMPLE'; // a simple value property

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

	//-------------------------------------------------------------------------------- $foreign_table
	/**
	 * Foreign table name
	 *
	 * @var string
	 */
	public $foreign_table;

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

	//-------------------------------------------------------------------------------------- toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->toSql() . ' [' . $this->type . ']';
	}

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * Generate a new Sql_Join instance, with initialization of all it's properties
	 *
	 * @param $mode           integer
	 * @param $master_alias   string
	 * @param $master_column  string
	 * @param $foreign_table  string
	 * @param $foreign_alias  string
	 * @param $foreign_column string
	 * @param $type           string
	 * @param $foreign_class  string
	 * @return Sql_Join
	 */
	public static function newInstance(
		$mode, $master_alias, $master_column, $foreign_alias, $foreign_table, $foreign_column,
		$type = self::SIMPLE, $foreign_class = null
	) {
		$sql_join = new Sql_Join();
		$sql_join->foreign_alias  = $foreign_alias;
		$sql_join->foreign_class  = isset($foreign_class) ? Builder::className($foreign_class) : null;
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
		return SP . $this->mode . ' JOIN ' . BQ . $this->foreign_table . BQ . SP . $this->foreign_alias
		. ' ON ' . $this->foreign_alias . DOT . $this->foreign_column
		. ' = ' . $this->master_alias . DOT . $this->master_column;
	}

}
