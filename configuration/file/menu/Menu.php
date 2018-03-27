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
	 * @param $block_title string
	 * @return Block
	 */
	public function addBlock($block_title)
	{
		$block = $this->searchBlock($block_title);
		if (!$block) {
			$block          = new Block();
			$block->items   = [];
			$block->title   = $block_title;
			$this->insertBlock($block);
		}
		return $block;
	}

	//------------------------------------------------------------------------------------- addBlocks
	/**
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

	//----------------------------------------------------------------------------------- insertBlock
	/**
	 * @param $block Block
	 */
	protected function insertBlock(Block $block)
	{
		$blocks = [];
		// search the key of the last menu block in the list
		$last_block = end($this->blocks);
		while (($last_block !== false) && !($last_block instanceof $block)) {
			$last_block = prev($this->blocks);
		}
		// copy existing blocks, and insert the new block at the right place
		$inserted       = false;
		$last_block_key = key($this->blocks);
		foreach ($this->blocks as $block_key => $existing_block) {
			// insert the new block before the existing block (alphabetical)
			if (($existing_block instanceof Block) && ($existing_block->title > $block->title)) {
				$blocks[] = $block;
				$inserted = true;
			}
			// insert the exiting block
			$blocks[] = $existing_block;
			// insert the new block immediately after the last existing block (not after strings)
			if (($block_key === $last_block_key) && !$inserted) {
				$blocks[] = $block;
				$inserted = true;
			}
		}
		// insert the new block at the end, if not already inserted (last chance)
		if (!$inserted) {
			$blocks[] = $block;
		}
		$this->blocks = $blocks;
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
