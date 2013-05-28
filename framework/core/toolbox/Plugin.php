<?php
namespace SAF\Framework;

/**
 * The Plugin interface must be used to define plugins
 */
interface Plugin
{

	//-------------------------------------------------------------------------------------- register
	/**
	 * Register code for the plugin
	 *
	 * Will be executed at plugin's initialization
	 */
	/** @noinspection PhpAbstractStaticMethodInspection */
	public static function register();

}
