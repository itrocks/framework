<?php
namespace SAF\Framework;

/**
 * Dao_Func shortcut class to all functions constructors
 */
abstract class Dao_Func
{

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

	//------------------------------------------------------------------------------------------- max
	/**
	 * @return Dao_Max_Function
	 */
	public static function max()
	{
		return new Dao_Max_Function();
	}

}
