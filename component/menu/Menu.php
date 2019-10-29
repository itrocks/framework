<?php
namespace ITRocks\Framework\Component;

use ITRocks\Framework\Component\Menu\Block;
use ITRocks\Framework\Component\Menu\Construct_Item;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Reflection\Annotation\Class_\Display_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Displays_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;

/**
 * A standard menu for your application
 */
class Menu implements Configurable
{
	use Construct_Item;
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
					/** @noinspection PhpUnhandledExceptionInspection $class_name must be valid */
					$link_class_name = in_array($feature, Feature::ON_SET)
						? Names::classToSet($class_name)
						: $class_name;
					$link_feature = (($feature === Feature::F_LIST) && !class_exists($link_class_name))
						? null
						: $feature;
					/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
					$configuration_items[View::link($link_class_name, $link_feature)] = ucfirst(
						in_array($feature, Feature::ON_SET)
							? Displays_Annotation::of(new Reflection_Class($class_name))->value
							: Display_Annotation::of(new Reflection_Class($class_name))->value
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
	protected function constructBlock($block_key, array $items)
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

	//-------------------------------------------------------------------------------- constructTitle
	/**
	 * @param $items string[]
	 */
	protected function constructTitle(array $items)
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
