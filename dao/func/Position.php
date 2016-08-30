<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Locale\Loc;
use SAF\Framework\Sql\Builder;
use SAF\Framework\Sql\Value;
use SAF\Framework\Widget\Data_List\Summary_Builder;

/**
 * Function search first position of string into another
 */
class Position implements Where
{

	//---------------------------------------------------------------------------------- POSITION_SQL
	const POSITION_SQL = 'LOCATE';

	//------------------------------------------------------------------------------------- $haystack
	/**
	 * String to look into
	 *
	 * @var string
	 */
	public $haystack;

	//--------------------------------------------------------------------------------------- $needle
	/**
	 * String to look for
	 *
	 * @var string
	 */
	public $needle;

	//--------------------------------------------------------------------------------------- $offset
	/**
	 * @varChar offset from start
	 */
	public $offset = 0;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Locate constructor
	 *
	 * @param $needle   string
	 * @param $haystack string
	 * @param $offset   integer
	 */
	public function __construct($needle, $haystack, $offset = 0)
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
	public function toHuman(Summary_Builder $builder, $property_path, $prefix = '')
	{
		return Loc::tr(Position::POSITION_SQL)
		. '('
		. (
			($this->needle instanceof Where)
			? $this->needle->toHuman($builder, $property_path, $prefix)
			: Value::escape($this->needle)
		) . ','
		. (
			($this->haystack instanceof Where)
			? $this->haystack->toHuman($builder, $property_path, $prefix)
			: Value::escape($this->haystack)
		) . ','
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
	public function toSql(Builder\Where $builder, $property_path, $prefix = '')
	{
		return Position::POSITION_SQL
		. '('
		. (
			($this->needle instanceof Where)
			? $this->needle->toSql($builder, $property_path, $prefix)
			: Value::escape($this->needle)
		) . ','
		. (
			($this->haystack instanceof Where)
			? $this->haystack->toSql($builder, $property_path, $prefix)
			: Value::escape($this->haystack)
		) . ','
		. $this->offset
		. ')';
	}

}
