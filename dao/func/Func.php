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
use ITRocks\Framework\Dao\Func\In_Select;
use ITRocks\Framework\Dao\Func\In_Set;
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
	 * @param $arguments mixed|Where[]
	 * @return Logical
	 */
	public static function andOp(mixed $arguments) : Logical
	{
		return new Logical(Logical::AND_OPERATOR, $arguments);
	}

	//--------------------------------------------------------------------------------------- average
	/**
	 * @return Group_By
	 */
	public static function average() : Group_By
	{
		return new Group_By(Group_By::AVERAGE);
	}

	//--------------------------------------------------------------------------------------- between
	/**
	 * @param $from float|int|string
	 * @param $to   float|int|string
	 * @return Range
	 */
	public static function between(float|int|string $from, float|int|string $to) : Range
	{
		return new Range($from, $to);
	}

	//---------------------------------------------------------------------------------------- concat
	/**
	 * @param $properties    string[]
	 * @param $property_path string|null If set, will return a key to the instantiated Concat object
	 * @return Concat|string
	 */
	public static function concat(array $properties, string $property_path = null) : Concat|string
	{
		return $property_path
			? Expressions::add($property_path, new Concat($properties))
			: new Concat($properties);
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * @return Group_By
	 */
	public static function count() : Group_By
	{
		return new Group_By(Group_By::COUNT);
	}

	//------------------------------------------------------------------------------------------- day
	/**
	 * @param $property_path string|null If set, will return a key to the instantiated Day object
	 * @return Day|string
	 */
	public static function day(string $property_path = null) : Day|string
	{
		return $property_path
			? Expressions::add($property_path, new Day())
			: new Day();
	}

	//-------------------------------------------------------------------------------------- distinct
	/**
	 * @return Call
	 */
	public static function distinct() : Call
	{
		return new Call(Call::DISTINCT);
	}

	//----------------------------------------------------------------------------------------- equal
	/**
	 * @param $value mixed
	 * @return Comparison
	 */
	public static function equal(mixed $value) : Comparison
	{
		return new Comparison(Comparison::EQUAL, $value);
	}

	//--------------------------------------------------------------------------------------- greater
	/**
	 * @param $value mixed
	 * @return Comparison
	 */
	public static function greater(mixed $value) : Comparison
	{
		return new Comparison(Comparison::GREATER, $value);
	}

	//-------------------------------------------------------------------------------- greaterOrEqual
	/**
	 * @param $value mixed
	 * @return Comparison
	 */
	public static function greaterOrEqual(mixed $value) : Comparison
	{
		return new Comparison(Comparison::GREATER_OR_EQUAL, $value);
	}

	//----------------------------------------------------------------------------------- groupConcat
	/**
	 * @param $property_path string|null If set, will return a key to the instantiated Group_Concat
	 *                                   object
	 * @param $separator     string|null Separator for the concat @default ,
	 * @return Group_Concat|string
	 */
	public static function groupConcat(string $property_path = null, string $separator = null)
		: Group_Concat|string
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
	public static function haveAll(array|Where $conditions) : Have_All
	{
		return new Have_All($conditions);
	}

	//-------------------------------------------------------------------------------------------- in
	/**
	 * @param $values array
	 * @return In
	 */
	public static function in(array $values) : In
	{
		return new In($values, true);
	}

	//-------------------------------------------------------------------------------------- inSelect
	/**
	 * @param $select Select
	 * @return In_Select
	 */
	public static function inSelect(Select $select) : In_Select
	{
		return new In_Select($select, true);
	}

	//----------------------------------------------------------------------------------------- inSet
	/**
	 * @param $value string[]
	 * @return In_Set
	 */
	public static function inSet(array $value) : In_Set
	{
		return new In_Set($value);
	}

	//------------------------------------------------------------------------------------ isGreatest
	/**
	 * @param $properties string[]
	 * @return Is_Greatest
	 */
	public static function isGreatest(array $properties) : Is_Greatest
	{
		return new Is_Greatest($properties);
	}

	//------------------------------------------------------------------------------------- isNotNull
	/**
	 * @return Comparison
	 */
	public static function isNotNull() : Comparison
	{
		return new Comparison(Comparison::NOT_EQUAL, null);
	}

	//---------------------------------------------------------------------------------------- isNull
	/**
	 * @return Comparison
	 */
	public static function isNull() : Comparison
	{
		return new Comparison(Comparison::EQUAL, null);
	}

	//------------------------------------------------------------------------------------------ left
	/**
	 * @param $property_path integer|string Optional : for use in WHERE clause only
	 * @param $length        integer|null
	 * @return Left|string
	 */
	public static function left(int|string $property_path, int $length = null) : Left|string
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
	public static function leftMatch(mixed $value) : Left_Match
	{
		return new Left_Match($value, true);
	}

	//------------------------------------------------------------------------------------------ less
	/**
	 * @param $value mixed
	 * @return Comparison
	 */
	public static function less(mixed $value) : Comparison
	{
		return new Comparison(Comparison::LESS, $value);
	}

	//----------------------------------------------------------------------------------- lessOrEqual
	/**
	 * @param $value mixed
	 * @return Comparison
	 */
	public static function lessOrEqual(mixed $value) : Comparison
	{
		return new Comparison(Comparison::LESS_OR_EQUAL, $value);
	}

	//------------------------------------------------------------------------------------------ like
	/**
	 * @param $value mixed
	 * @return Comparison
	 */
	public static function like(mixed $value) : Comparison
	{
		return new Comparison(Comparison::LIKE, $value);
	}

	//------------------------------------------------------------------------------------------- max
	/**
	 * @return Group_By
	 */
	public static function max() : Group_By
	{
		return new Group_By(Group_By::MAX);
	}

	//------------------------------------------------------------------------------------------- min
	/**
	 * @return Group_By
	 */
	public static function min() : Group_By
	{
		return new Group_By(Group_By::MIN);
	}

	//----------------------------------------------------------------------------------------- month
	/**
	 * @param $property_path string|null If set, will return a key to the instantiated Month object
	 * @return Month|string
	 */
	public static function month(string $property_path = null) : Month|string
	{
		return $property_path
			? Expressions::add($property_path, new Month())
			: new Month();
	}

	//------------------------------------------------------------------------------------ notBetween
	/**
	 * @param $from float|int|string
	 * @param $to   float|int|string
	 * @return Range
	 */
	public static function notBetween(float|int|string $from, float|int|string $to) : Range
	{
		return new Range($from, $to, false);
	}

	//-------------------------------------------------------------------------------------- notEqual
	/**
	 * @param $value mixed
	 * @return Comparison
	 */
	public static function notEqual(mixed $value) : Comparison
	{
		return new Comparison(Comparison::NOT_EQUAL, $value);
	}

	//----------------------------------------------------------------------------------------- notIn
	/**
	 * @param $values array
	 * @return In
	 */
	public static function notIn(array $values) : In
	{
		return new In($values, false);
	}

	//-------------------------------------------------------------------------------------- notInSet
	/**
	 * @param $value string[]
	 * @return In_Set
	 */
	public static function notInSet(array $value) : In_Set
	{
		return new In_Set($value, true);
	}

	//--------------------------------------------------------------------------------------- notLike
	/**
	 * @param $value mixed
	 * @return Comparison
	 */
	public static function notLike(mixed $value) : Comparison
	{
		return new Comparison(Comparison::NOT_LIKE, $value);
	}

	//--------------------------------------------------------------------------------------- notNull
	/**
	 * @return Comparison
	 */
	public static function notNull() : Comparison
	{
		return new Comparison(Comparison::NOT_EQUAL, null);
	}

	//----------------------------------------------------------------------------------------- notOp
	/**
	 * @param $value mixed
	 * @return Logical
	 */
	public static function notOp(mixed $value) : Logical
	{
		return new Logical(Logical::NOT_OPERATOR, $value);
	}

	//------------------------------------------------------------------------------------------- now
	/**
	 * @param $where boolean if true, get an Expression(new Now) to use it into a where clause
	 * @return Left|string
	 */
	public static function now(bool $where = false) : Left|string
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
	public static function orOp(mixed $arguments) : Logical
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
	public static function position(string $needle, string $haystack, int $offset = 0) : Position
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
	public static function property(string $property_path, string $prefix = '') : Property
	{
		return new Property($property_path, $prefix);
	}

	//------------------------------------------------------------------------------------------- sum
	/**
	 * @param $property_path string|null
	 * @return Group_By|string
	 */
	public static function sum(string $property_path = null) : Group_By|string
	{
		return $property_path
			? Expressions::add($property_path, new Group_By(Group_By::SUM))
			: new Group_By(Group_By::SUM);
	}

	//------------------------------------------------------------------------------------------ trim
	/**
	 * @return Call
	 */
	public static function trim() : Call
	{
		return new Call(Call::TRIM);
	}

	//------------------------------------------------------------------------------------- trimester
	/**
	 * @param $property_path string|null If set, will return a key of instantiated Trimester object
	 * @return string|Trimester
	 */
	public static function trimester(string $property_path = null) : string|Trimester
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
	public static function value(mixed $value) : Value
	{
		return new Value($value);
	}

	//----------------------------------------------------------------------------------------- xorOp
	/**
	 * @param $arguments Where[]|mixed
	 * @return Logical
	 */
	public static function xorOp(mixed $arguments) : Logical
	{
		return new Logical(Logical::XOR_OPERATOR, $arguments);
	}

	//------------------------------------------------------------------------------------------ year
	/**
	 * @param $property_path string|null If set, will return a key to the instantiated Year object
	 * @return string|Year
	 */
	public static function year(string $property_path = null) : string|Year
	{
		return $property_path
			? Expressions::add($property_path, new Year())
			: new Year();
	}

}
