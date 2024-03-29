<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework;
use ITRocks\Framework\Component\Menu\Block;
use ITRocks\Framework\Component\Menu\Item;
use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Menu\Exhaustive;
use ITRocks\Framework\Plugin\Installable\Installed;

/**
 * The menu.php configuration file
 */
class Menu extends File
{

	//--------------------------------------------------------------------------------------- $blocks
	/**
	 * @var Block[]
	 */
	public array $blocks;

	//-------------------------------------------------------------------------------------- addBlock
	/**
	 * Add a menu block or return the existing block
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $block_title string
	 * @return Block
	 */
	public function addBlock(string $block_title) : Block
	{
		$block = $this->searchBlock($block_title);
		if (!$block) {
			/** @noinspection PhpUnhandledExceptionInspection class */
			$block        = Framework\Builder::create(Block::class);
			$block->items = [];
			$block->title = $block_title;
			(new Exhaustive($this))->addBlock($block);
		}
		return $block;
	}

	//------------------------------------------------------------------------------------- addBlocks
	/**
	 * Add several blocks and their items to the menu
	 *
	 * @param $blocks array string $item_caption[string $block_title][string $item_link]
	 */
	public function addBlocks(array $blocks) : void
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $block        Block|string
	 * @param $item_link    string
	 * @param $item_caption string
	 * @return Item
	 */
	public function addItem(Block|string $block, string $item_link, string $item_caption) : Item
	{
		if (is_string($block)) {
			$block = $this->addBlock($block);
		}
		(new Installed\Menu)->add($block->title, $item_link, $item_caption);
		$item = $this->searchItem($block, $item_link);
		if (!$item) {
			/** @noinspection PhpUnhandledExceptionInspection class */
			$item           = Framework\Builder::create(Item::class);
			$item->link     = $item_link;
			(new Exhaustive($this))->addItem($block, $item);
		}
		$item->caption = $item_caption;
		return $item;
	}

	//-------------------------------------------------------------------------------------- addItems
	/**
	 * Add several items to the given menu block (auto-create block and items)
	 *
	 * @param $block Block|string
	 * @param $items string[] string $item_caption[string $item_link]
	 */
	public function addItems(Block|string $block, array $items) : void
	{
		if (is_string($block)) {
			$block = $this->addBlock($block);
		}
		foreach ($items as $item_link => $item_caption) {
			$this->addItem($block, $item_link, $item_caption);
		}
	}

	//--------------------------------------------------------------------------------------- hasLink
	/**
	 * @param $link string
	 * @return boolean
	 */
	public function hasLink(string $link) : bool
	{
		foreach ($this->blocks as $block) {
			foreach ($block->items as $item) {
				if (($item instanceof Item) && $item->link === $link) {
					return true;
				}
			}
		}
		return false;
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read from file
	 */
	public function read() : void
	{
		(new Menu\Reader($this))->read();
	}

	//---------------------------------------------------------------------------------- removeBlocks
	/**
	 * @param $blocks array string $item_caption[string $block_title][string $item_link]
	 */
	public function removeBlocks(array $blocks) : void
	{
		// mark menu blocks / items as removed, without removing them
		foreach ($blocks as $block_title => $block) {
			foreach ($block as $item_link => $item_caption) {
				$removed = (new Installed\Menu)->remove($block_title, $item_link, $item_caption);
				// do not remove the entry from the configuration file if it is still used by other features
				if ($removed && $removed->features) {
					unset($blocks[$block_title][$item_link]);
				}
			}
		}
		// remove all unused menu items / blocks
		foreach ($this->blocks as $block_key => $block) {
			if (($block instanceof Block) && isset($blocks[$block->title])) {
				foreach ($block->items as $item_key => $item) {
					if (($item instanceof Item) && isset($blocks[$block->title][$item->link])) {
						unset($block->items[$item_key]);
						if (!$block->items) {
							unset($this->blocks[$block_key]);
						}
					}
				}
			}
		}
	}

	//----------------------------------------------------------------------------------- searchBlock
	/**
	 * Search a menu block
	 *
	 * @param $block_title string
	 * @return ?Block
	 */
	public function searchBlock(string $block_title) : ?Block
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
	 * @return ?Item
	 */
	public function searchItem(Block|string $block, string $item_caption_link) : ?Item
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
	public function write() : void
	{
		(new Menu\Writer($this))->write();
	}

}
