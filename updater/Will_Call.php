<?php
namespace ITRocks\Framework\Updater;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Session;

/**
 * This singleton is stored into the session, and lists all callables that have to be called after
 * the next update
 */
class Will_Call
{

	//------------------------------------------------------------------------------------ $callables
	/**
	 * @var callable[] An additional element indexed 2 can contain an updates count before running
	 */
	public $callables = [];

	//------------------------------------------------------------------------------------------- add
	/**
	 * You can call this to ask for a call to $callable after next update
	 *
	 * @param $callable callable
	 */
	public static function add($callable)
	{
		$will_call = Session::current()->get(static::class, true);
		array_push($will_call->callables, $callable);
	}

	//------------------------------------------------------------------------------------------ call
	/**
	 * Call all waiting callables now, and reset session queue
	 */
	public static function call()
	{
		$will_call = Session::current()->get(static::class);
		if (!$will_call) {
			return;
		}
		foreach ($will_call->callables as $key => &$callable) {
			if (isset($callable[2])) {
				$callable[2] --;
				if (!$callable[2]) {
					unset($callable[2]);
				}
				continue;
			}
			if (is_array($callable) && is_string($callable[0])) {
				$callable[0] = Builder::className($callable[0]);
			}
			call_user_func($callable);
			unset($will_call->callables[$key]);
		}
		if (!$will_call->callables) {
			Session::current()->remove($will_call);
		}
	}

	//---------------------------------------------------------------------------------------- isDone
	/**
	 * Returns true if there is no trailing callables
	 *
	 * @return boolean
	 */
	public static function isDone()
	{
		return !Session::current()->get(static::class);
	}

}
