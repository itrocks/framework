<?php
namespace SAF\Framework;

/**
 * Dao_Func shortcut class to all functions constructors
 */
abstract class Dao_Func
{

	//------------------------------------------------------------------------------------------- and
	/**
	 * @var $arguments Dao_Where_Function[]|mixed[]
	 * @return Dao_Logical_Function
	 */
	public static function andOp($arguments)
	{
		return new Dao_Logical_Function(Dao_Logical_Function::AND_OPERATOR, $arguments);
	}

	//--------------------------------------------------------------------------------------- average
	/**
	 * @return Dao_Group_By_Function
	 */
	public static function average()
	{
		return new Dao_Group_By_Function(Dao_Group_By_Function::AVERAGE);
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * @return Dao_Group_By_Function
	 */
	public static function count()
	{
		return new Dao_Group_By_Function(Dao_Group_By_Function::COUNT);
	}

	//----------------------------------------------------------------------------------------- equal
	/**
	 * @param $value mixed
	 * @return Dao_Comparison_Function
	 */
	public static function equal($value)
	{
		return new Dao_Comparison_Function(Dao_Comparison_Function::EQUAL, $value);
	}

	//--------------------------------------------------------------------------------------- greater
	/**
	 * @param $value mixed
	 * @return Dao_Comparison_Function
	 */
	public static function greater($value)
	{
		return new Dao_Comparison_Function(Dao_Comparison_Function::GREATER, $value);
	}

	//-------------------------------------------------------------------------------- greaterOrEqual
	/**
	 * @param $value mixed
	 * @return Dao_Comparison_Function
	 */
	public static function greaterOrEqual($value)
	{
		return new Dao_Comparison_Function(Dao_Comparison_Function::GREATER_OR_EQUAL, $value);
	}

	//------------------------------------------------------------------------------------ isGreatest
	/**
	 * @param $properties string[]
	 * @return Dao_Is_Greatest_Function
	 */
	public static function isGreatest($properties)
	{
		return new Dao_Is_Greatest_Function($properties);
	}

	//------------------------------------------------------------------------------------------ left
	/**
	 * @param $length integer
	 * @return Dao_Left_Function
	 */
	public static function left($length)
	{
		return new Dao_Left_Function($length);
	}

	//------------------------------------------------------------------------------------- leftMatch
	/**
	 * @param $value mixed
	 * @return Dao_Left_Match_Function
	 */
	public static function leftMatch($value)
	{
		return new Dao_Left_Match_Function($value);
	}

	//------------------------------------------------------------------------------------------ less
	/**
	 * @param $value mixed
	 * @return Dao_Comparison_Function
	 */
	public static function less($value)
	{
		return new Dao_Comparison_Function(Dao_Comparison_Function::LESS, $value);
	}

	//----------------------------------------------------------------------------------- lessOrEqual
	/**
	 * @param $value mixed
	 * @return Dao_Comparison_Function
	 */
	public static function lessOrEqual($value)
	{
		return new Dao_Comparison_Function(Dao_Comparison_Function::LESS_OR_EQUAL, $value);
	}

	//------------------------------------------------------------------------------------------ like
	/**
	 * @param $value mixed
	 * @return Dao_Comparison_Function
	 */
	public static function like($value)
	{
		return new Dao_Comparison_Function(Dao_Comparison_Function::LIKE, $value);
	}

	//------------------------------------------------------------------------------------------- max
	/**
	 * @return Dao_Group_By_Function
	 */
	public static function max()
	{
		return new Dao_Group_By_Function(Dao_Group_By_Function::MAX);
	}

	//------------------------------------------------------------------------------------------- min
	/**
	 * @return Dao_Group_By_Function
	 */
	public static function min()
	{
		return new Dao_Group_By_Function(Dao_Group_By_Function::MIN);
	}

	//-------------------------------------------------------------------------------------- notEqual
	/**
	 * @param $value mixed
	 * @return Dao_Comparison_Function
	 */
	public static function notEqual($value)
	{
		return new Dao_Comparison_Function(Dao_Comparison_Function::NOT_EQUAL, $value);
	}

	//--------------------------------------------------------------------------------------- notLike
	/**
	 * @param $value mixed
	 * @return Dao_Comparison_Function
	 */
	public static function notLike($value)
	{
		return new Dao_Comparison_Function(Dao_Comparison_Function::NOT_LIKE, $value);
	}

	//--------------------------------------------------------------------------------------- notNull
	/**
	 * @return Dao_Comparison_Function
	 */
	public static function notNull()
	{
		return new Dao_Comparison_Function(Dao_Comparison_Function::NOT_EQUAL, null);
	}

	//------------------------------------------------------------------------------------------ orOp
	/**
	 * @var $arguments Dao_Where_Function[]|mixed[]
	 * @return Dao_Logical_Function
	 */
	public static function orOp($arguments)
	{
		return new Dao_Logical_Function(Dao_Logical_Function::OR_OPERATOR, $arguments);
	}

	//------------------------------------------------------------------------------------------- sum
	/**
	 * @return Dao_Group_By_Function
	 */
	public static function sum()
	{
		return new Dao_Group_By_Function(Dao_Group_By_Function::SUM);
	}

	//----------------------------------------------------------------------------------------- xorOp
	/**
	 * @var $arguments Dao_Where_Function[]|mixed[]
	 * @return Dao_Logical_Function
	 */
	public static function xorOp($arguments)
	{
		return new Dao_Logical_Function(Dao_Logical_Function::XOR_OPERATOR, $arguments);
	}

}
