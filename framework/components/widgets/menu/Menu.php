<?php
namespace SAF\Framework;

use SAF\Plugins;

/**
 * A standard menu for your application
 */
class Menu implements Plugins\Configurable
{

	//--------------------------------------------------------------------------------------- $blocks
	/**
	 * @var Menu_Block[]
	 */
	public $blocks;

	//---------------------------------------------------------------------------------------- $title
	/**
	 * @var string
	 */
	public $title;

	//----------------------------------------------------------------------------------- $title_link
	/**
	 * link for the title
	 *
	 * @var string
	 */
	public $title_link;

	//---------------------------------------------------------------------------- $title_link_target
	/**
	 * target of the title link
	 *
	 * @var string
	 */
	public $title_link_target;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct($configuration)
	{
		foreach ($configuration as $block_key => $items) {
			if (is_numeric($block_key)) {
				foreach ($items as $item) {
					if     (substr($item, 0, 1) == "/") $this->title_link        = $item;
					elseif (substr($item, 0, 1) == "#") $this->title_link_target = $item;
					else                                $this->title             = $item;
				}
			}
			else {
				$block = new Menu_Block();
				if (substr($block_key, 0, 1) == "/") $block->title_link = $block_key;
				else                                 $block->title      = $block_key;
				foreach ($items as $item_key => $item) {
					if     ($item_key == "module") $block->module            = $item;
					elseif ($item_key == "title")  $block->title             = $item;
					elseif ($item_key == "link")   $block->title_link        = $item;
					elseif ($item_key == "target") $block->title_link_target = $item;
					else {
						$menu_item = new Menu_Item();
						$menu_item->link = $item_key;
						if (is_array($item)) {
							foreach ($item as $property_key => $property) {
								if (is_numeric($property_key)) {
									if     (substr($property, 0, 1) == "/") $menu_item->link        = $property;
									elseif (substr($property, 0, 1) == "#") $menu_item->link_target = $property;
									else                                    $menu_item->caption     = $property;
								}
							}
						}
						else {
							$menu_item->caption = $item;
						}
						$block->items[] = $menu_item;
					}
				}
				if (!isset($block->module)) {
					$block->module = Names::displayToProperty($block_key);
				}
				$this->blocks[] = $block;
			}
		}
	}

}
