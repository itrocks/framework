<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Feature\List_\Summary_Builder;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Value;

/**
 * Function search first position of string into another
 */
class Position implements Where
{
	use Has_To_String;

	//---------------------------------------------------------------------------------- POSITION_SQL
	const POSITION_SQL = 'LOCATE';

	//------------------------------------------------------------------------------------- $haystack
	/**
	 * String to look into
	 *
	 * @var string|Where
	 */
	public string|Where $haystack;

	//--------------------------------------------------------------------------------------- $needle
	/**
	 * String to look for
	 *
	 * @var string|Where
	 */
	public string|Where $needle;

	//--------------------------------------------------------------------------------------- $offset
	/**
	 * @varChar offset from start
	 */
	public int $offset = 0;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Locate constructor
	 *
	 * @param $needle   string|Where
	 * @param $haystack string|Where
	 * @param $offset   integer
	 */
	public function __construct(string|Where $needle, string|Where $haystack, int $offset = 0)
	{
		$this->needle   = $needle;
		$this->haystack = $haystack;
		$this->offset   = $offset;
	}

	//--------------------------------------------------------------------------------------- toHuman
	/**
	 * Returns the Dao function as Human readable string
	 *
	 * @param $builder       Summary_Builder the sql query builder
	 * @param $property_path string the property path
	 * @param $prefix        string column name prefix
	 * @return string
	 */
	public function toHuman(Summary_Builder $builder, string $property_path, string $prefix = '')
		: string
	{
		return Loc::tr(Position::POSITION_SQL)
		. '('
		. (
			($this->needle instanceof Where)
			? $this->needle->toHuman($builder, $property_path, $prefix)
			: Value::escape($this->needle)
		) . ', '
		. (
			($this->haystack instanceof Where)
			? $this->haystack->toHuman($builder, $property_path, $prefix)
			: Value::escape($this->haystack)
		) . ', '
		. $this->offset
		. ')';
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       Builder\Where the sql query builder
	 * @param $property_path string the property path
	 * @param $prefix        string column name prefix
	 * @return string
	 */
	public function toSql(Builder\Where $builder, string $property_path, string $prefix = '') : string
	{
		return Position::POSITION_SQL
		. '('
		. (
			($this->needle instanceof Where)
			? $this->needle->toSql($builder, $property_path, $prefix)
			: Value::escape($this->needle)
		) . ', '
		. (
			($this->haystack instanceof Where)
			? $this->haystack->toSql($builder, $property_path, $prefix)
			: Value::escape($this->haystack)
		) . ', '
		. $this->offset
		. ')';
	}

}
