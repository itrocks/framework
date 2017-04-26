<?php
namespace ITRocks\Framework\Dao;

use ITRocks\Framework\Dao\Func\Call;
use ITRocks\Framework\Dao\Func\Column;
use ITRocks\Framework\Dao\Func\Comparison;
use ITRocks\Framework\Dao\Func\Group_By;
use ITRocks\Framework\Dao\Func\Group_Concat;
use ITRocks\Framework\Dao\Func\In;
use ITRocks\Framework\Dao\Func\InSelect;
use ITRocks\Framework\Dao\Func\Is_Greatest;
use ITRocks\Framework\Dao\Func\Left;
use ITRocks\Framework\Dao\Func\Left_Match;
use ITRocks\Framework\Dao\Func\Logical;
use ITRocks\Framework\Dao\Func\Position;
use ITRocks\Framework\Dao\Func\Property;
use ITRocks\Framework\Dao\Func\Range;
use ITRocks\Framework\Dao\Func\Where;
use ITRocks\Framework\Sql\Builder\Select;

/**
 * Dao_Func shortcut class to all functions constructors
 */
abstract class Func
{

	//----------------------------------------------------------------------------------------- andOp
	/**
	 * @var $arguments Where[]|mixed
	 * @return Logical
	 */
	public static function andOp($arguments)
	{
		return new Logical(Logical::AND_OPERATOR, $arguments);
	}

	//--------------------------------------------------------------------------------------- average
	/**
	 * @return Group_By
	 */
	public static function average()
	{
		return new Group_By(Group_By::AVERAGE);
	}

	//--------------------------------------------------------------------------------------- between
	/**
	 * @param $from mixed
	 * @param $to   mixed
	 * @return Range
	 */
	public static function between($from, $to)
	{
		return new Range($from, $to);
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * @return Group_By
	 */
	public static function count()
	{
		return new Group_By(Group_By::COUNT);
	}

	//-------------------------------------------------------------------------------------- distinct
	/**
	 * @return Call
	 */
	public static function distinct()
	{
		return new Call(Call::DISTINCT);
	}

	//----------------------------------------------------------------------------------------- equal
	/**
	 * @param $value mixed
	 * @return Comparison
	 */
	public static function equal($value)
	{
		return new Comparison(Comparison::EQUAL, $value);
	}

	//--------------------------------------------------------------------------------------- greater
	/**
	 * @param $value mixed
	 * @return Comparison
	 */
	public static function greater($value)
	{
		return new Comparison(Comparison::GREATER, $value);
	}

	//-------------------------------------------------------------------------------- greaterOrEqual
	/**
	 * @param $value mixed
	 * @return Comparison
	 */
	public static function greaterOrEqual($value)
	{
		return new Comparison(Comparison::GREATER_OR_EQUAL, $value);
	}

	//----------------------------------------------------------------------------------- groupConcat
	/**
	 * @param $column    Column|string Property path or Func\Column.
	 *                   Default will be the associated property path.
	 * @param $separator string Separator for the concat @default ,
	 * @return Group_Concat
	 */
	public function groupConcat($column = null, $separator = null)
	{
		return new Group_Concat($column, $separator);
	}

	//-------------------------------------------------------------------------------------------- in
	/**
	 * @param $values array
	 * @return In
	 */
	public static function in(array $values)
	{
		return new In($values);
	}

	//-------------------------------------------------------------------------------------- inSelect
	/**
	 * @param $select Select
	 * @return InSelect
	 */
	public static function inSelect(Select $select)
	{
		return new InSelect($select);
	}

	//------------------------------------------------------------------------------------ isGreatest
	/**
	 * @param $properties string[]
	 * @return Is_Greatest
	 */
	public static function isGreatest(array $properties)
	{
		return new Is_Greatest($properties);
	}

	//---------------------------------------------------------------------------------------- isNull
	/**
	 * @return Comparison
	 */
	public static function isNull()
	{
		return new Comparison(Comparison::EQUAL, null);
	}

	//------------------------------------------------------------------------------------------ left
	/**
	 * @param $length integer
	 * @return Left
	 */
	public static function left($length)
	{
		return new Left($length);
	}

	//------------------------------------------------------------------------------------- leftMatch
	/**
	 * @param $value mixed
	 * @return Left_Match
	 */
	public static function leftMatch($value)
	{
		return new Left_Match($value);
	}

	//------------------------------------------------------------------------------------------ less
	/**
	 * @param $value mixed
	 * @return Comparison
	 */
	public static function less($value)
	{
		return new Comparison(Comparison::LESS, $value);
	}

	//----------------------------------------------------------------------------------- lessOrEqual
	/**
	 * @param $value mixed
	 * @return Comparison
	 */
	public static function lessOrEqual($value)
	{
		return new Comparison(Comparison::LESS_OR_EQUAL, $value);
	}

	//------------------------------------------------------------------------------------------ like
	/**
	 * @param $value mixed
	 * @return Comparison
	 */
	public static function like($value)
	{
		return new Comparison(Comparison::LIKE, $value);
	}

	//------------------------------------------------------------------------------------------- max
	/**
	 * @return Group_By
	 */
	public static function max()
	{
		return new Group_By(Group_By::MAX);
	}

	//------------------------------------------------------------------------------------------- min
	/**
	 * @return Group_By
	 */
	public static function min()
	{
		return new Group_By(Group_By::MIN);
	}

	//------------------------------------------------------------------------------------ notBetween
	/**
	 * @param $from mixed
	 * @param $to   mixed
	 * @return Range
	 */
	public static function notBetween($from, $to)
	{
		return new Range($from, $to, true);
	}

	//-------------------------------------------------------------------------------------- notEqual
	/**
	 * @param $value mixed
	 * @return Comparison
	 */
	public static function notEqual($value)
	{
		return new Comparison(Comparison::NOT_EQUAL, $value);
	}

	//----------------------------------------------------------------------------------------- notIn
	/**
	 * @param $values array
	 * @return In
	 */
	public static function notIn(array $values)
	{
		return new In($values, true);
	}

	//--------------------------------------------------------------------------------------- notLike
	/**
	 * @param $value mixed
	 * @return Comparison
	 */
	public static function notLike($value)
	{
		return new Comparison(Comparison::NOT_LIKE, $value);
	}

	//--------------------------------------------------------------------------------------- notNull
	/**
	 * @return Comparison
	 */
	public static function notNull()
	{
		return new Comparison(Comparison::NOT_EQUAL, null);
	}

	//----------------------------------------------------------------------------------------- notOp
	/**
	 * @param $value mixed
	 * @return Logical
	 */
	public static function notOp($value)
	{
		return new Logical(Logical::NOT_OPERATOR, $value);
	}

	//------------------------------------------------------------------------------------------ orOp
	/**
	 * @var $arguments Where[]|mixed
	 * @return Logical
	 */
	public static function orOp($arguments)
	{
		return new Logical(Logical::OR_OPERATOR, $arguments);
	}

	//-------------------------------------------------------------------------------------- position
	/**
	 * @param $needle   string
	 * @param $haystack string
	 * @param $offset   integer
	 * @return Position
	 */
	public static function position($needle, $haystack, $offset = 0)
	{
		return new Position($needle, $haystack, $offset);
	}

	//-------------------------------------------------------------------------------------- property
	/**
	 * Gets property for use in function
	 *
	 * @param $property_path string the property path
	 * @param $prefix        string column name prefix
	 * @return Property
	 */
	public static function property($property_path, $prefix = '')
	{
		return new Property($property_path, $prefix);
	}

	//------------------------------------------------------------------------------------------- sum
	/**
	 * @return Group_By
	 */
	public static function sum()
	{
		return new Group_By(Group_By::SUM);
	}

	//------------------------------------------------------------------------------------------ trim
	/**
	 * @return Call
	 */
	public static function trim()
	{
		return new Call(Call::TRIM);
	}

	//----------------------------------------------------------------------------------------- xorOp
	/**
	 * @var $arguments Where[]|mixed
	 * @return Logical
	 */
	public static function xorOp($arguments)
	{
		return new Logical(Logical::XOR_OPERATOR, $arguments);
	}

}
