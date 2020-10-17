<?php
namespace ITRocks\Framework\Dao;

use ITRocks\Framework\Dao\Func\Call;
use ITRocks\Framework\Dao\Func\Comparison;
use ITRocks\Framework\Dao\Func\Concat;
use ITRocks\Framework\Dao\Func\Day;
use itrocks\framework\dao\func\Expressions;
use ITRocks\Framework\Dao\Func\Group_By;
use ITRocks\Framework\Dao\Func\Group_Concat;
use ITRocks\Framework\Dao\Func\Have_All;
use ITRocks\Framework\Dao\Func\In;
use ITRocks\Framework\Dao\Func\In_Set;
use ITRocks\Framework\Dao\Func\InSelect;
use ITRocks\Framework\Dao\Func\Is_Greatest;
use ITRocks\Framework\Dao\Func\Left;
use ITRocks\Framework\Dao\Func\Left_Match;
use ITRocks\Framework\Dao\Func\Logical;
use ITRocks\Framework\Dao\Func\Month;
use ITRocks\Framework\Dao\Func\Now;
use ITRocks\Framework\Dao\Func\Position;
use ITRocks\Framework\Dao\Func\Property;
use ITRocks\Framework\Dao\Func\Range;
use ITRocks\Framework\Dao\Func\Trimester;
use ITRocks\Framework\Dao\Func\Value;
use ITRocks\Framework\Dao\Func\Where;
use ITRocks\Framework\Dao\Func\Year;
use ITRocks\Framework\Sql\Builder\Select;

/**
 * Dao_Func shortcut class to all functions constructors
 *
 * Could be abstract, but we need a generic empty instance for controllers
 *
 * @abstract
 */
class Func
{

	//----------------------------------------------------------------------------------------- andOp
	/**
	 * @param $arguments Where[]|mixed
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

	//---------------------------------------------------------------------------------------- concat
	/**
	 * @param $properties    string[]
	 * @param $property_path string If set, will return a key to the instantiated Concat object
	 * @return Concat|string
	 */
	public static function concat(array $properties, $property_path = null)
	{
		return $property_path
			? Expressions::add($property_path, new Concat($properties))
			: new Concat($properties);
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * @return Group_By
	 */
	public static function count()
	{
		return new Group_By(Group_By::COUNT);
	}

	//------------------------------------------------------------------------------------------- day
	/**
	 * @param $property_path string If set, will return a key to the instantiated Day object
	 * @return Day|string
	 */
	public static function day($property_path = null)
	{
		return $property_path
			? Expressions::add($property_path, new Day())
			: new Day();
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
	 * @param $property_path string If set, will return a key to the instantiated Group_Concat object
	 * @param $separator string Separator for the concat @default ,
	 * @return Group_Concat|string
	 */
	public static function groupConcat($property_path = null, $separator = null)
	{
		return $property_path
			? Expressions::add($property_path, new Group_Concat($separator))
			: new Group_Concat($separator);
	}

	//--------------------------------------------------------------------------------------- haveAll
	/**
	 * @param $conditions array|Where
	 * @return Have_All
	 */
	public static function haveAll($conditions)
	{
		return new Have_All($conditions);
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

	//----------------------------------------------------------------------------------------- inSet
	/**
	 * @param $value string
	 * @return In_Set
	 */
	public static function inSet($value)
	{
		return new In_Set($value);
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

	//------------------------------------------------------------------------------------- isNotNull
	/**
	 * @return Comparison
	 */
	public static function isNotNull()
	{
		return new Comparison(Comparison::NOT_EQUAL, null);
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
	 * @param $property_path string|integer Optional : for use in WHERE clause only
	 * @param $length        integer
	 * @return Left|string
	 */
	public static function left($property_path, $length = null)
	{
		return isset($length)
			? Expressions::add($property_path, new Left($length))
			: new Left($property_path);
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

	//----------------------------------------------------------------------------------------- month
	/**
	 * @param $property_path string If set, will return a key to the instantiated Month object
	 * @return Month|string
	 */
	public static function month($property_path = null)
	{
		return $property_path
			? Expressions::add($property_path, new Month())
			: new Month();
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

	//-------------------------------------------------------------------------------------- notInSet
	/**
	 * @param $value string
	 * @return In_Set
	 */
	public static function notInSet($value)
	{
		return new In_Set($value, true);
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

	//------------------------------------------------------------------------------------------- now
	/**
	 * @param $where boolean if true, get an Expression(new Now) to use it into a where clause
	 * @return Left|string
	 */
	public static function now($where = false)
	{
		return $where
			? Expressions::add(null, new Now())
			: new Now();
	}

	//------------------------------------------------------------------------------------------ orOp
	/**
	 * @param $arguments Where[]|mixed
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
	 * @param $property_path string
	 * @return Group_By|string
	 */
	public static function sum($property_path = null)
	{
		return $property_path
			? Expressions::add($property_path, new Group_By(Group_By::SUM))
			: new Group_By(Group_By::SUM);
	}

	//------------------------------------------------------------------------------------------ trim
	/**
	 * @return Call
	 */
	public static function trim()
	{
		return new Call(Call::TRIM);
	}

	//------------------------------------------------------------------------------------- trimester
	/**
	 * @param $property_path string If set, will return a key to the instantiated Trimester object
	 * @return Trimester|string
	 */
	public static function trimester($property_path = null)
	{
		return $property_path
			? Expressions::add($property_path, new Trimester())
			: new Trimester();
	}

	//----------------------------------------------------------------------------------------- value
	/**
	 * @param $value mixed
	 * @return Value
	 */
	public static function value($value)
	{
		return new Value($value);
	}

	//----------------------------------------------------------------------------------------- xorOp
	/**
	 * @param $arguments Where[]|mixed
	 * @return Logical
	 */
	public static function xorOp($arguments)
	{
		return new Logical(Logical::XOR_OPERATOR, $arguments);
	}

	//------------------------------------------------------------------------------------------ year
	/**
	 * @param $property_path string If set, will return a key to the instantiated Year object
	 * @return Year|string
	 */
	public static function year($property_path = null)
	{
		return $property_path
			? Expressions::add($property_path, new Year())
			: new Year();
	}

}
