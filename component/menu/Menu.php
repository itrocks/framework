<?php
namespace ITRocks\Framework\Component;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Component\Menu\Block;
use ITRocks\Framework\Component\Menu\Construct_Item;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;
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

	//---------------------------------------------------------------------------------------- BLOCKS
	const BLOCKS = 'blocks';

	//--------------------------------------------------------------------------------------- $blocks
	/**
	 * @var Block[]
	 */
	public $blocks = [];

	//-------------------------------------------------------------------------- $configuration_items
	/**
	 * Linearized list of items, as they are into the original configuration
	 *
	 * @var string[]
	 */
	public $configuration_items = [];

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
			$this->blocks              = [];
			$this->configuration_items = [];
			foreach ($configuration as $block_key => $items) {
				if ($block_key == self::TITLE) {
					$this->constructTitle($items);
				}
				else {
					$this->configuration_items[] = $items;
					$block                       = $this->constructBlock($block_key, $items);
					if ($block) {
						$this->blocks[] = $block;
					}
				}
			}
			$this->configuration_items = array_merge(...$this->configuration_items);
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
	 * @param $class_names string|string[]|object class name(s), can be multiple in one or several
	 *                      arguments. If string[] : key can be class name, then value is the feature.
	 *                      If object, will build a configuration to access this object.
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
					$feature    = is_string($class_name) ? Feature::F_LIST : null;
				}
				if (is_object($class_name)) {
					$object_link = View::link($class_name, $feature);
					$class_name  = get_class($class_name);
					$configuration_items[$object_link] = ucfirst(Names::classToDisplay($class_name));
				}
				// class name : change it to a menu item
				elseif (strpos($class_name, BS))  {
					$link_class_name = in_array($feature, Feature::ON_SET)
						? Names::classToSet($class_name)
						: $class_name;
					$link_feature = (($feature === Feature::F_LIST) && !class_exists($link_class_name))
						? null
						: $feature;
					$configuration_items[View::link($link_class_name, $link_feature)] = ucfirst(
						in_array($feature, Feature::ON_SET)
							? Names::classToDisplays($class_name)
							: Names::classToDisplay($class_name)
					);
				}
			}
		}
		return $configuration_items;
	}

	//-------------------------------------------------------------------------------- constructBlock
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $block_key string
	 * @param $items     array
	 * @return Block
	 */
	protected function constructBlock($block_key, array $items)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		$block = Builder::create(Block::class);

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

	//--------------------------------------------------------------------------------------- refresh
	/**
	 * Refresh the menu on the front interface, if placed into the standard target (#menu)
	 */
	public function refresh()
	{
		Main::$current->redirect(
			View::link(
				static::class,
				Feature::F_OUTPUT,
				null,
				[Parameter::CONTAINER => static::BLOCKS]
			),
			Target::MENU
		);
	}

}
