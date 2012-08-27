<?php

abstract class View
{

	/**
	 * @var View
	 */
	private static $current_view;

	//------------------------------------------------------------------------------------ getCurrent
	/**
	 * @return View
	 */
	public static function getCurrent()
	{
		return View::$current_view;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param array $uri
	 * @param array $get
	 * @param array $post
	 * @param array $files
	 */
	public abstract function run($uri, $get, $post, $files);

	//------------------------------------------------------------------------------------ setCurrent
	/**
	 * @param View $current_view
	 */
	public static function setCurrent($current_view)
	{
		View::$current_view = $current_view;
	}

}
