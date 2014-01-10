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
	 * @return Dao_And_Function
	 */
	public static function andOp($arguments)
	{
		return new Dao_And_Function($arguments);
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
	 * @return Dao_Less_Function
	 */
	public static function less($value)
	{
		return new Dao_Less_Function($value);
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
	 * @return Dao_Max_Function
	 */
	public static function max()
	{
		return new Dao_Max_Function();
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

	//------------------------------------------------------------------------------------------ orOp
	/**
	 * @var $arguments Dao_Where_Function[]|mixed[]
	 * @return Dao_Or_Function
	 */
	public static function orOp($arguments)
	{
		return new Dao_Or_Function($arguments);
	}

}
