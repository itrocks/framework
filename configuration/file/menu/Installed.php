<?php
namespace ITRocks\Framework\Configuration\File\Menu;

use ITRocks\Framework\Configuration\File;

/**
 * Installed menu
 *
 * @store_name installed_menus
 */
class Installed extends File\Installed
{

	//---------------------------------------------------------------------------------------- $block
	/**
	 * @var string
	 */
	public $block;

	//-------------------------------------------------------------------------------------- $caption
	/**
	 * @var string
	 */
	public $caption;

	//----------------------------------------------------------------------------------------- $link
	/**
	 * @var string
	 */
	public $link;

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $block   string
	 * @param $link    string
	 * @param $caption string
	 * @return static
	 */
	public static function add($block, $link, $caption)
	{
		return static::addProperties(['block' => $block, 'link' => $link, 'caption' => $caption]);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * @param $block   string
	 * @param $link    string
	 * @param $caption string
	 * @return static
	 */
	public static function remove($block, $link, $caption)
	{
		return static::removeProperties(['block' => $block, 'link' => $link, 'caption' => $caption]);
	}

}
