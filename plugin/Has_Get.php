<?php
namespace ITRocks\Framework\Plugin;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Plugin;
use ITRocks\Framework\Session;

/**
 * Allow plugins to be accessible with a static method get()
 *
 * All plugins should use this trait
 *
 * @extends Plugin
 * @see Plugin
 */
trait Has_Get
{

	//------------------------------------------------------------------------------------------- get
	/**
	 * Retrieves static implementation form session
	 *
	 * @param $default boolean if false, will not instantiate a non-registered plugin
	 * @return Plugin|static|null null only if default is false and the plugin is not registered
	 */
	public static function get(bool $default = true) : Plugin|static|null
	{
		return ($default || static::registered())
			? Session::current()->plugins->get(Builder::className(static::class))
			: null;
	}

	//------------------------------------------------------------------------------------ registered
	/**
	 * @return boolean
	 */
	public static function registered() : bool
	{
		return Session::current()->plugins->has(Builder::className(static::class));
	}

}
