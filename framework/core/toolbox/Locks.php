<?php
namespace SAF\Framework;

/**
 * Locks manager, to enable locking of features and disable multiple execution of the same thing the same time
 */
abstract class Locks
{

	private static $locks = [];

	//---------------------------------------------------------------------------------------- delock
	/**
	 * Decrement the given lock name, and unlock if done
	 *
	 * If lock name is still locked : return false
	 * If lock name is unlocked : return true
	 *
	 * @param $lock_name string
	 * @return boolean
	 */
	public static function delock($lock_name)
	{
		Locks::$locks[$lock_name]--;
		if (!Locks::$locks[$lock_name]) {
			unset(Locks::$locks[$lock_name]);
			return true;
		}
		return false;
	}

	//------------------------------------------------------------------------------------------ lock
	/**
	 * Locks the given lock name
	 *
	 * At first call : set lock to true and return false
	 * At next calls until noRecurseEnd() is called : return true to stop recursivity
	 *
	 * @param $lock_name string
	 * @return boolean
	 */
	public static function lock($lock_name)
	{
		if (isset(Locks::$locks[$lock_name])) {
			Locks::$locks[$lock_name]++;
			return true;
		}
		Locks::$locks[$lock_name] = 1;
		return false;
	}

	//---------------------------------------------------------------------------------------- unlock
	/**
	 * Unlock the given lock name, even if it has been locked several times
	 *
	 * @param $lock_name string
	 */
	public static function unlock($lock_name)
	{
		if (isset(Locks::$locks[$lock_name])) {
			unset(Locks::$locks[$lock_name]);
		}
	}

}
