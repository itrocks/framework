<?php
namespace ITRocks\Framework\Component;

use ITRocks\Framework\Component\Menu\Construct_Item;
use ITRocks\Framework\Component\Menu\Item;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;

/**
 * Quick menu : like a menu, but with only a few items
 *
 * Made to be always visible
 */
class Quick_Menu implements Configurable
{
	use Construct_Item;
	use Has_Get;

	//---------------------------------------------------------------------------------------- $items
	/**
	 * @var Item[]
	 */
	public $items;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration mixed
	 */
	public function __construct($configuration = null)
	{
		if (isset($configuration)) {
			$this->items = [];
			foreach ($configuration as $item_key => $item) {
				$this->items[] = $this->constructItem($item_key, $item);
			}
		}
	}

}
