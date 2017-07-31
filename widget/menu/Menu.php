<?php
namespace ITRocks\Framework\Widget;

use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Widget\Menu\Block;
use ITRocks\Framework\Widget\Menu\Item;

/**
 * A standard menu for your application
 */
class Menu implements Configurable
{

	//------------------------------------------------------- Menu configuration array keys constants

	//------------------------------------------------------------------------------------------- ALL
	const ALL = ':';

	//----------------------------------------------------------------------------------------- CLEAR
	const CLEAR = 'clear';

	//------------------------------------------------------------------------------------------ LINK
	const LINK = 'link';

	//---------------------------------------------------------------------------------------- MODULE
	const MODULE = 'module';

	//---------------------------------------------------------------------------------------- TARGET
	const TARGET = 'target';

	//----------------------------------------------------------------------------------------- TITLE
	const TITLE = 'title';

	//--------------------------------------------------------------------------------------- $blocks
	/**
	 * @var Block[]
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
	public function __construct($configuration = null)
	{
		if (isset($configuration)) {
			$this->blocks = [];
			foreach ($configuration as $block_key => $items) {
				if ($block_key == self::TITLE) {
					$this->constructTitle($items);
				}
				else {
					$block = $this->constructBlock($block_key, $items);
					if ($block) {
						$this->blocks[] = $block;
					}
				}
			}
		}
	}

	//-------------------------------------------------------------------------------- constructBlock
	/**
	 * @param $block_key string
	 * @param $items     array
	 * @return Block
	 */
	private function constructBlock($block_key, array $items)
	{
		$block = new Block();

		if (substr($block_key, 0, 1) == SL) {
			$block->title_link = $block_key;
		}
		else {
			$block->title = $block_key;
		}

		foreach ($items as $item_key => $item) {
			if     ($item_key == self::MODULE) $block->module            = $item;
			elseif ($item_key == self::TITLE)  $block->title             = $item;
			elseif ($item_key == self::LINK)   $block->title_link        = $item;
			elseif ($item_key == self::TARGET) $block->title_link_target = $item;
			else {
				$menu_item = $this->constructItem($item_key, $item);
				if ($menu_item) {
					$block->items[] = $menu_item;
				}
			}
		}
		if (!$block->items) {
			$block = null;
		}
		elseif (!isset($block->module)) {
			$block->module = Names::displayToProperty($block_key);
		}
		return $block;
	}

	//--------------------------------------------------------------------------------- constructItem
	/**
	 * @param $item_key string
	 * @param $item     string[]|string
	 * @return Item
	 */
	private function constructItem($item_key, $item)
	{
		if ($item === static::CLEAR) {
			return null;
		}
		$menu_item = new Item();
		$menu_item->link = $item_key;
		if (is_array($item)) {
			foreach ($item as $property_key => $property) {
				if (is_numeric($property_key)) {
					if     (substr($property, 0, 1) == SL)  $menu_item->link        = $property;
					elseif (substr($property, 0, 1) == '#') $menu_item->link_target = $property;
					else                                    $menu_item->caption     = $property;
				}
			}
		}
		else {
			$menu_item->caption = $item;
		}
		return $menu_item;
	}

	//-------------------------------------------------------------------------------- constructTitle
	/**
	 * @param $items string[]
	 */
	private function constructTitle(array $items)
	{
		foreach ($items as $item) {
			switch (substr($item, 0, 1)) {
				case SL:  $this->title_link        = $item; break;
				case '#': $this->title_link_target = $item; break;
				default:  $this->title = $item;
			}
		}
	}

}
