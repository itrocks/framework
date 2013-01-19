<?php
namespace SAF\Framework;

class Menu_Builder_Configuration
{

	//----------------------------------------------------------------------------------------- build
	/**
	 * Build a menu using a configuration recursive array
	 *
	 * @param array $configuration
	 * @return Menu
	 */
	public function build($configuration)
	{
		$menu = new Menu();
		foreach ($configuration as $key => $value) {
			if (is_numeric($key)) {
				foreach ($value as $val) {
					if     (substr($val, 0, 1) == "/") $menu->title_link        = $val;
					elseif (substr($val, 0, 1) == "#") $menu->title_link_target = $val;
					else                               $menu->title             = $val;
				}
			}
			else {
				$block = new Menu_Block();
				if (substr($key, 0, 1) == "/") $block->title_link  = $key;
				else                           $block->title       = $key;
				foreach ($value as $key => $value) {
					if     ($key == "color")  $block->color            = $value;
					elseif ($key == "title")  $block->title             = $value;
					elseif ($key == "link")   $block->title_link        = $value;
					elseif ($key == "target") $block->title_link_target = $value;
					else {
						$item = new Menu_Item();
						$item->caption  = $value;
						$item->link     = $key;
						$block->items[] = $item;
					}
				}
				$menu->blocks[] = $block;
			}
		}
		return $menu;
	}

}
