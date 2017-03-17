<?php

namespace ITRocks\Framework\Plugin;

use ITRocks\Framework\Plugin;
use ITRocks\Framework\Session;

/**
 * Allow plugin to be accessible with static method get
 */
trait Is_Plugin_Singleton
{

	//------------------------------------------------------------------------------------------- get
	/**
	 * Retrieves static implementation form session
	 *
	 * @return Plugin | static
	 */
	public static function get()
	{
		/** @var $plugin Plugin | static */
		$plugin = Session::current()->plugins->get(static::class);
		return $plugin;
	}
}