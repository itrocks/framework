<?php
namespace SAF\Framework;

class Menu_Builder_Configuration
{

	//----------------------------------------------------------------------------------------- build
	/**
	 * Build a menu using a configuration recursive array
	 *
	 * @param $configuration array
	 * @return Menu
	 */
	public function build($configuration)
	{
		$menu = new Menu();
		foreach ($configuration as $block_key => $items) {
			if (is_numeric($block_key)) {
				foreach ($items as $item) {
					if     (substr($item, 0, 1) == "/") $menu->title_link        = $item;
					elseif (substr($item, 0, 1) == "#") $menu->title_link_target = $item;
					else                                $menu->title             = $item;
				}
			}
			else {
				$block = new Menu_Block();
				if (substr($block_key, 0, 1) == "/") $block->title_link = $block_key;
				else                                 $block->title      = $block_key;
				foreach ($items as $item_key => $item) {
					if     ($item_key == "color")  $block->color             = $item;
					elseif ($item_key == "title")  $block->title             = $item;
					elseif ($item_key == "link")   $block->title_link        = $item;
					elseif ($item_key == "target") $block->title_link_target = $item;
					else {
						$menu_item = new Menu_Item();
						$menu_item->caption = $item;
						$menu_item->link = $item_key;
						$block->items[] = $menu_item;
					}
				}
				$menu->blocks[] = $block;
			}
		}
		return $menu;
	}

}
