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

	//---------------------------------------------------------------------------------- $block_title
	/**
	 * @var string
	 */
	public $block_title;

	//--------------------------------------------------------------------------------- $item_caption
	/**
	 * @var string
	 */
	public $item_caption;

	//------------------------------------------------------------------------------------ $item_link
	/**
	 * @var string
	 */
	public $item_link;

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $block_title  string
	 * @param $item_link    string
	 * @param $item_caption string
	 * @return static
	 */
	public function add($block_title, $item_link, $item_caption)
	{
		return $this->addProperties(
			['block_title' => $block_title, 'item_link' => $item_link, 'item_caption' => $item_caption]
		);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * @param $block_title  string
	 * @param $item_link    string
	 * @param $item_caption string
	 * @return static
	 */
	public function remove($block_title, $item_link, $item_caption)
	{
		return $this->removeProperties(
			['block_title' => $block_title, 'item_link' => $item_link, 'item_caption' => $item_caption]
		);
	}

}
