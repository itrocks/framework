<?php
namespace SAF\Framework;

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
