<?php
namespace SAF\Framework;

/*
 * @representative login
 */
class User
{
	use Account, Current { current as private pCurrent; }

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current User
	 * @return User
	 */
	public static function current($set_current = null)
	{
		return self::pCurrent($set_current);
	}

}
