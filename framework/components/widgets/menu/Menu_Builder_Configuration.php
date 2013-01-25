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
		foreach ($configuration as $key => $values) {
			if (is_numeric($key)) {
				foreach ($values as $value) {
					if     (substr($value, 0, 1) == "/") $menu->title_link        = $value;
					elseif (substr($value, 0, 1) == "#") $menu->title_link_target = $value;
					else                                 $menu->title             = $value;
				}
			}
			else {
				$block = new Menu_Block();
				if (substr($key, 0, 1) == "/") $block->title_link  = $key;
				else                           $block->title       = $key;
				foreach ($values as $sub_key => $value) {
					if     ($key == "color")  $block->color             = $value;
					elseif ($key == "title")  $block->title             = $value;
					elseif ($key == "link")   $block->title_link        = $value;
					elseif ($key == "target") $block->title_link_target = $value;
					else {
						$item = new Menu_Item();
						$item->caption  = $value;
						$item->link     = $sub_key;
						$block->items[] = $item;
					}
				}
				$menu->blocks[] = $block;
			}
		}
		return $menu;
	}

}
