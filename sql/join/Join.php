<?php
namespace ITRocks\Framework\Sql;

use ITRocks\Framework;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
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

	//------------------------------------------------------------------------------------------ LINK
	const LINK = 'LINK';

	//------------------------------------------------------------------------------ Values CONSTANTS
	const MODES = [self::INNER, self::LEFT, self::OUTER, self::RIGHT];
	const TYPES = [self::OBJECT, self::SIMPLE];

	//------------------------------------------------------------------------------------- JOIN TYPE
	const OBJECT = 'OBJECT';
	const SIMPLE = 'SIMPLE';

	//-------------------------------------------------------------------------------- $foreign_alias
	/** Alias for foreign table (t1..tn) */
	public string $foreign_alias = '';

	//-------------------------------------------------------------------------------- $foreign_class
	/** Foreign class name. Can be null if foreign table has no associated class (ie link table) */
	public string $foreign_class = '';

	//------------------------------------------------------------------------------- $foreign_column
	/** Foreign column name */
	public string $foreign_column = '';

	//----------------------------------------------------------------------------- $foreign_property
	/** The property that matches the foreign column (if set) */
	public ?Reflection_Property $foreign_property = null;

	//-------------------------------------------------------------------------------- $foreign_table
	/** Foreign table name */
	public string $foreign_table = '';

	//----------------------------------------------------------------------------------------- $like
	/** @var boolean[] key 0 : primary join, keys 1..n : secondary joins */
	public array $like = [];

	//---------------------------------------------------------------------------------- $linked_join
	public ?Join $linked_join = null;

	//--------------------------------------------------------------------------------- $master_alias
	/** Alias for master table (t0..tn) */
	public string $master_alias = '';

	//-------------------------------------------------------------------------------- $master_column
	/** Master column name */
	public string $master_column = '';

	//------------------------------------------------------------------------------ $master_property
	/** The property that matches the master column (if set) */
	public Reflection_Property $master_property;

	//----------------------------------------------------------------------------------------- $mode
	#[Values(self::MODES, '')]
	public string $mode = '';

	//------------------------------------------------------------------------------------ $secondary
	/**
	 * Secondary conditions for the join
	 * Key is the foreign column name, value if 'constant' / "constant" / master column name
	 *
	 * @var string[]
	 */
	public array $secondary = [];

	//----------------------------------------------------------------------------------------- $type
	/** Join column type */
	#[Values(self::TYPES)]
	public string $type = self::SIMPLE;

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->toSql() . ' [' . $this->type . ']';
	}

	//------------------------------------------------------------------------------------ foreignSql
	public function foreignSql() : string
	{
		return $this->foreign_alias . DOT . $this->foreign_column;
	}

	//------------------------------------------------------------------------------------- masterSql
	public function masterSql() : string
	{
		return $this->master_alias . DOT . $this->master_column;
	}

	//----------------------------------------------------------------------------------- newInstance
	/** Generate a new Sql_Join instance, with initialization of all its properties */
	public static function newInstance(
		string $mode, string $master_alias, string $master_column, string $foreign_alias,
		string $foreign_table, string $foreign_column, string $type = self::SIMPLE,
		string $foreign_class = ''
	) : static
	{
		$sql_join = new Join();
		$sql_join->foreign_alias  = $foreign_alias;
		$sql_join->foreign_class  = $foreign_class ? Framework\Builder::className($foreign_class) : '';
		$sql_join->foreign_column = $foreign_column;
		$sql_join->foreign_table  = $foreign_table;
		$sql_join->master_alias   = $master_alias;
		$sql_join->master_column  = $master_column;
		$sql_join->mode           = $mode;
		$sql_join->type           = $type;
		return $sql_join;
	}

	//----------------------------------------------------------------------------------------- toSql
	public function toSql() : string
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
