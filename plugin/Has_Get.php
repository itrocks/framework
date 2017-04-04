<?php
namespace ITRocks\Framework\Plugin;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Plugin;
use ITRocks\Framework\Session;

/**
 * Allow plugins to be accessible with a static method get()
 *
 * All plugins should use this trait
 */
trait Has_Get
{

	//------------------------------------------------------------------------------------------- get
	/**
	 * Retrieves static implementation form session
	 *
	 * @return Plugin|static
	 */
	public static function get()
	{
		/** @var $plugin Plugin|static */
		$plugin = Session::current()->plugins->get(Builder::className(static::class));
		return $plugin;
	}

}
