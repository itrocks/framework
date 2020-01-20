<?php
namespace ITRocks\Framework\Component\Menu;

/**
 * For those which have a constructItem method
 */
trait Construct_Item
{

	//--------------------------------------------------------------------------------- constructItem
	/**
	 * @param $item_key string
	 * @param $item     string[]|string
	 * @return Item
	 */
	protected function constructItem($item_key, $item)
	{
		if ($item === static::CLEAR) {
			return null;
		}
		$menu_item = new Item();
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
