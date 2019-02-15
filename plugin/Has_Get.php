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
	 * @param $default boolean if true, a default instance of the plugin is created is not set
	 * @return static
	 */
	public static function get($default = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection static::class is always valid */
		/** @var $plugin static */
		$plugin = Session::current()->plugins->get(Builder::className(static::class));
		if ($default && !$plugin) {
			static $default_instance;
			if (!$default_instance) {
				$default_instance = new static;
			}
			return $default_instance;
		}
		return $plugin;
	}

}
