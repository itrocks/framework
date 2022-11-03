<?php
namespace ITRocks\Framework\Plugin\Installable\Installed;

use ITRocks\Framework\Plugin\Installable\Installed;

/**
 * An installed menu (into menu.php)
 *
 * @store_name installed_menus
 */
class Menu extends Installed
{

	//---------------------------------------------------------------------------------- $block_title
	/**
	 * @var string
	 */
	public string $block_title;

	//--------------------------------------------------------------------------------- $item_caption
	/**
	 * @var string
	 */
	public string $item_caption;

	//------------------------------------------------------------------------------------ $item_link
	/**
	 * @var string
	 */
	public string $item_link;

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $block_title  string
	 * @param $item_link    string
	 * @param $item_caption string
	 * @return static
	 */
	public function add(string $block_title, string $item_link, string $item_caption) : static
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
	public function remove(string $block_title, string $item_link, string $item_caption) : static
	{
		return $this->removeProperties(
			['block_title' => $block_title, 'item_link' => $item_link, 'item_caption' => $item_caption]
		);
	}

}
