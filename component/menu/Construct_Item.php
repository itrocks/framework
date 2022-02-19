<?php
namespace ITRocks\Framework\Component\Menu;

use ITRocks\Framework\Builder;

/**
 * For those which have a constructItem method
 */
trait Construct_Item
{

	//--------------------------------------------------------------------------------- constructItem
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $item_key string
	 * @param $item     string[]|string
	 * @return ?Item
	 */
	protected function constructItem(string $item_key, array|string $item) : ?Item
	{
		if ($item === static::CLEAR) {
			return null;
		}
		/** @noinspection PhpUnhandledExceptionInspection class */
		$menu_item = Builder::create(Item::class);
		$menu_item->link = $item_key;
		if (is_array($item)) {
			foreach ($item as $property_key => $property) {
				if (is_numeric($property_key)) {
					if (substr($property, 0, 1) === SL) {
						$menu_item->link = $property;
					}
					elseif (in_array(substr($property, 0, 1), ['#', '_'])) {
						$menu_item->link_target = $property;
					}
					else {
						$menu_item->caption = $property;
					}
				}
			}
		}
		else {
			$menu_item->caption = $item;
		}
		return $menu_item;
	}

}
