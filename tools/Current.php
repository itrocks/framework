<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Plugin;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Session;

/**
 * All classes that have a global current value should use this trait to manage the current() method
 *
 * @example
 * The current() method should be overridden to improve IDE auto-completion capability, like this
 * class A_Class
 * {
 *   use Current { current as private pCurrent; }
 *   // doc-comment here with param $set_current A_Class and return A_Class annotations
 *   public static function current(self $set_current = null) : ?static
 *   {
 *     return self::pCurrent($set_current);
 *   }
 * }
 * @example
 * Then override classes should override current too :
 * class Another_Class extends A_Class
 * {
 *   // doc-comment here with param $set_current Another_Class and return Another_Class annotations
 *   public static function current(self $set_current = null) : ?static
 *   {
 *     return parent::current($set_current);
 *   }
 * }
 * @see User::current() for an example of use
 */
trait Current
{

	//-------------------------------------------------------------------------------------- $current
	/**
	 * @var ?object
	 */
	protected static ?object $current = null;

	//--------------------------------------------------------------------------------------- current
	/**
	 * Gets/sets current environment's object
	 *
	 * @param $set_current object|null
	 * @return ?object
	 */
	public static function current(object $set_current = null) : ?object
	{
		$called_class = static::class;

		// set current (ignore Reflection_Property : to enable use of #Default Class::current)
		if ($set_current && !is_a($set_current, Reflection_Property::class)) {
			static::$current = $set_current;
			if (!is_a($called_class, Plugin::class, true)) {
				Session::current()->set(
					$set_current, Builder::current()->sourceClassName($called_class)
				);
			}
		}

		// get current
		elseif (!isset(static::$current)) {

			// get current plugin from plugins manager
			if (is_a($called_class, Plugin::class, true)) {
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

			// get current value from session
			else {
				static::$current = Session::current()->get($called_class);
			}
		}

		return static::$current;
	}

	//---------------------------------------------------------------------------------- unsetCurrent
	/**
	 * Unset the current value
	 */
	public static function unsetCurrent() : void
	{
		static::$current = null;
		Session::current()->remove(Builder::current()->sourceClassName(static::class));
	}

}
