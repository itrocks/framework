<?php
namespace SAF\Framework\Tools;

use SAF\Framework\Builder;
use SAF\Framework\Plugin;
use SAF\Framework\Session;

/**
 * All classes that have a global current value should use this trait to manage the current() method
 *
 * The current() method should be overridden to improve IDE's auto-completion capability, like this
 * @example
 * class A_Class
 * {
 *   use Current { current as private pCurrent; }
 *   // doc-comment here with param $set_current A_Class and return A_Class annotations
 *   public static function current($set_current = null)
 *   {
 *     return self::pCurrent($set_current);
 *   }
 * }
 * @example
 * Then overriden classes should override current too :
 * class Another_Class extends A_Class
 * {
 *   // doc-comment here with param $set_current Another_Class and return Another_Class annotations
 *   public static function current($set_current = null)
 *   {
 *     return parent::current($set_current);
 *   }
 * }
 * @See User::current() for an example of use
 */
trait Current
{

	//-------------------------------------------------------------------------------------- $current
	/**
	 * @var object
	 */
	protected static $current = null;

	//--------------------------------------------------------------------------------------- current
	/**
	 * Gets/sets current environment's object
	 *
	 * @param $set_current mixed
	 * @return Current
	 */
	public static function current($set_current = null)
	{
		$called_class = get_called_class();
		if ($set_current) {
			static::$current = $set_current;
			if (!is_a($called_class, Plugin::class, true)) {
				Session::current()->set(
					$set_current, Builder::current()->sourceClassName(get_called_class())
				);
			}
		}
		elseif (!isset(static::$current) && is_a($called_class, Plugin::class, true)) {
			if ($called_class === Builder::class) {
				static::$current = new Builder();
			}
			else {
				$plugin = Session::current()->plugins->get(
					Builder::current()->sourceClassName($called_class)
				);
				if (!isset(static::$current)) {
					static::$current = $plugin;
				}
			}
		}
		return static::$current;
	}

}
