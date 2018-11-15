<?php
namespace ITRocks\Framework\Widget;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;
use ITRocks\Framework\Widget\Menu\Block;
use ITRocks\Framework\Widget\Menu\Item;

/**
 * A standard menu for your application
 */
class Menu implements Configurable
{
	use Has_Get;

	//------------------------------------------------------- Menu configuration array keys constants
	/**
	 * @deprecated Menu::CLEAR without the key Menu::ALL will be enough : remove ALL
	 */
	const ALL    = ':';
	const LINK   = 'link';
	const MODULE = 'module';
	const TARGET = 'target';
	const TITLE  = 'title';

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

	//------------------------------------------------------------------------------- configurationOf
	/**
	 * Build a menu configuration for a given feature (list is the default), given a list of
	 * class names.
	 *
	 * - The feature is Feature::F_LIST at start if no other feature begins the list
	 * - Each time a string without BS is read : it is the name of feature for the next classes
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_names string|string[] class name(s), can be multiple in one or several arguments
	 *                     If string[] : key can be class name, then value is the feature
	 * @return string[] key is the URI to call the feature, value if the caption of the menu item
	 */
	public static function configurationOf($class_names)
	{
		$configuration_items = [];
		foreach (func_get_args() as $class_names) {
			if (!is_array($class_names)) {
				$class_names = [$class_names];
			}
			foreach ($class_names as $class_name => $feature) {
				if (is_numeric($class_name)) {
					$class_name = $feature;
					$feature    = Feature::F_LIST;
				}
				// class name : change it to a menu item
				if (strpos($class_name, BS))  {
					if (in_array($feature, Feature::ON_SET)) {
						/** @noinspection PhpUnhandledExceptionInspection $class_name must be valid */
						$class_name = Names::classToSet($class_name);
					}
					$link_feature = (($feature === Feature::F_LIST) && !class_exists($class_name))
						? null
						: $feature;
					$configuration_items[View::link($class_name, $link_feature)] = ucfirst(
						Names::classToDisplay($class_name)
					);
				}
			}
		}
		return $configuration_items;
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
