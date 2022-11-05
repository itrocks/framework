<?php
namespace ITRocks\Framework\Configuration\File\Menu;

use ITRocks\Framework\Application;
use ITRocks\Framework\Component;
use ITRocks\Framework\Component\Menu\Block;
use ITRocks\Framework\Component\Menu\Item;
use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Tools\Value_Lists;

/**
 * Exhaustive menu file
 */
class Exhaustive
{

	//--------------------------------------------------------------------------------------- SECTION
	const SECTION = 'ordering';

	//----------------------------------------------------------------------------------- $exhaustive
	/**
	 * Cache for exhaustiveMenus()
	 *
	 * @return string[][][] three dimensions : list, block, item : if no block title
	 */
	protected array $exhaustive;

	//----------------------------------------------------------------------------------------- $menu
	/**
	 * @var File\Menu
	 */
	public File\Menu $menu;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $menu File\Menu
	 */
	public function __construct(File\Menu $menu)
	{
		$this->menu = $menu;
	}

	//-------------------------------------------------------------------------------------- addBlock
	/**
	 * @param $block Block
	 */
	public function addBlock(Block $block) : void
	{
		// get menu blocks
		$blocks     = [];
		$title_menu = null;
		foreach ($this->menu->blocks as $menu_block) {
			if ($menu_block->title === 'Menu::TITLE') {
				$title_menu = $menu_block;
			}
			else {
				$blocks[$menu_block->title] = $menu_block;
			}
		}
		// exhaustive list of block titles
		$lists = [];
		foreach ($this->exhaustiveMenus() as $list) {
			$lists[] = array_keys($list);
		}
		$lists[]      = array_keys($blocks);
		$block_titles = (new Value_Lists($lists))->assembly();
		// regenerated menu blocks
		$this->menu->blocks = [];
		foreach ($block_titles as $block_title) {
			if (isset($blocks[$block_title])) {
				$this->menu->blocks[] = $blocks[$block_title];
			}
			elseif ($block_title === $block->title) {
				$this->menu->blocks[] = $block;
				$found                = true;
			}
		}
		if (!isset($found)) {
			array_unshift($this->menu->blocks, $block);
		}
		if ($title_menu) {
			array_unshift($this->menu->blocks, $title_menu);
		}
	}

	//--------------------------------------------------------------------------------------- addItem
	/**
	 * @param $block Block
	 * @param $item  Item
	 */
	public function addItem(Block $block, Item $item) : void
	{
		// get menu items
		$first_items = [];
		$items       = [];
		foreach ($block->items as $block_item) {
			if (is_string($block_item)) {
				$first_items[] = $block_item;
			}
			else {
				$items[$block_item->link] = $block_item;
			}
		}
		// exhaustive list of item links
		$lists = [];
		foreach ($this->exhaustiveMenus() as $list) {
			if (isset($list[$block->title])) {
				$lists[] = $list[$block->title];
			}
		}
		$lists[]    = array_keys($items);
		$item_links = (new Value_Lists($lists))->assembly();
		// regenerated block items
		$block->items = $first_items;
		foreach ($item_links as $item_link) {
			if (isset($items[$item_link])) {
				$block->items[] = $items[$item_link];
			}
			elseif ($item_link === $item->link) {
				$block->items[] = $item;
				$found          = true;
			}
		}
		if (!isset($found)) {
			$block->items[] = $item;
		}
	}

	//------------------------------------------------------------------------------- exhaustiveMenus
	/**
	 * @return string[][][] three dimensions : list, block, item : if no block title
	 */
	protected function exhaustiveMenus() : array
	{
		if (!isset($this->exhaustive)) {
			$lists = [];
			foreach (Application::current()->getClassesTree(true) as $application_class) {
				$directory = strtolower(str_replace(BS, SL, lLastParse($application_class, BS)));
				$file_path = $directory . SL . 'exhaustive.yaml';
				if (file_exists($file_path)) {
					$data = yaml_parse_file($file_path);
					if (isset($data[Component\Menu::class][static::SECTION])) {
						$lists[] = $data[Component\Menu::class][static::SECTION];
					}
				}
			}
			$this->exhaustive = array_reverse($lists);
		}
		return $this->exhaustive;
	}

}
