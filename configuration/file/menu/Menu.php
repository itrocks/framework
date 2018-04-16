<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Widget\Menu\Block;
use ITRocks\Framework\Widget\Menu\Item;

/**
 * The menu.php configuration file
 */
class Menu extends File
{

	//--------------------------------------------------------------------------------------- $blocks
	/**
	 * @var Block[]
	 */
	public $blocks;

	//-------------------------------------------------------------------------------------- addBlock
	/**
	 * Add a menu block or return the existing block
	 *
	 * @param $block_title string
	 * @return Block
	 */
	public function addBlock($block_title)
	{
		$block = $this->searchBlock($block_title);
		if (!$block) {
			$block        = new Block();
			$block->items = [];
			$block->title = $block_title;
			$this->blocks = objectInsertSorted($this->blocks, $block, 'title');
		}
		return $block;
	}

	//------------------------------------------------------------------------------------- addBlocks
	/**
	 * Add several blocks and their items to the menu
	 *
	 * @param $blocks array string $item_caption[string $block_title][string $item_link]
	 */
	public function addBlocks(array $blocks)
	{
		foreach ($blocks as $block_title => $items) {
			$block = $this->addBlock($block_title);
			$this->addItems($block, $items);
		}
	}

	//--------------------------------------------------------------------------------------- addItem
	/**
	 * Add a menu item or return the existing item
	 *
	 * @param $block        Block|string
	 * @param $item_link    string
	 * @param $item_caption string
	 * @return Item
	 */
	public function addItem($block, $item_link, $item_caption)
	{
		if (is_string($block)) {
			$block = $this->addBlock($block);
		}
		$item = $this->searchItem($block, $item_link);
		if (!$item) {
			$item           = new Item();
			$item->link     = $item_link;
			$block->items[] = $item;
		}
		$item->caption  = $item_caption;
		return $item;
	}

	//-------------------------------------------------------------------------------------- addItems
	/**
	 * Add several items to the given menu block (auto-create block and items)
	 *
	 * @param $block Block|string
	 * @param $items string[] string $item_caption[string $item_link]
	 */
	public function addItems($block, array $items)
	{
		if (is_string($block)) {
			$block = $this->addBlock($block);
		}
		foreach ($items as $item_link => $item_caption) {
			$this->addItem($block, $item_link, $item_caption);
		}
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read from file
	 */
	public function read()
	{
		(new Menu\Reader($this))->read();
	}

	//----------------------------------------------------------------------------------- searchBlock
	/**
	 * Search a menu block
	 *
	 * @param $block_title string
	 * @return Block|null
	 */
	public function searchBlock($block_title)
	{
		foreach ($this->blocks as $block) {
			if (($block instanceof Block) && ($block->title === $block_title)) {
				return $block;
			}
		}
		return null;
	}

	//------------------------------------------------------------------------------------ searchItem
	/**
	 * Search an item into a menu block
	 *
	 * @param $block             Block|string
	 * @param $item_caption_link string item caption or link
	 * @return Item|null
	 */
	public function searchItem($block, $item_caption_link)
	{
		if (is_string($block)) {
			$block = $this->searchBlock($block);
		}
		foreach ($block->items as $item) {
			if (
				($item instanceof Item)
				&& in_array($item_caption_link, [$item->caption, $item->link], true)
			) {
				return $item;
			}
		}
		return null;
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * Write to file
	 */
	public function write()
	{
		(new Menu\Writer($this))->write();
	}

}
