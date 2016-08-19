<?php
namespace SAF\Framework\Dao;

use SAF\Framework\Dao\Func\Call;
use SAF\Framework\Dao\Func\Comparison;
use SAF\Framework\Dao\Func\Group_By;
use SAF\Framework\Dao\Func\In;
use SAF\Framework\Dao\Func\Is_Greatest;
use SAF\Framework\Dao\Func\Left;
use SAF\Framework\Dao\Func\Left_Match;
use SAF\Framework\Dao\Func\Logical;
use SAF\Framework\Dao\Func\Position;
use SAF\Framework\Dao\Func\Where;

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

	//-------------------------------------------------------------------------------------------- in
	/**
	 * @param $values mixed[]
	 * @return In
	 */
	public static function in($values)
	{
		return new In($values);
	}

	//------------------------------------------------------------------------------------ isGreatest
	/**
	 * @param $properties string[]
	 * @return Is_Greatest
	 */
	public static function isGreatest($properties)
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
	 * @param $values mixed[]
	 * @return In
	 */
	public static function notIn($values)
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
	 * @param $needle
	 * @param $haystack
	 * @param int $offset
	 * @return Position
	 */
	public static function position($needle, $haystack, $offset = 0)
	{
		return new Position($needle, $haystack, $offset);
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
