<?php
namespace ITRocks\Framework\Tools\Feature_Class;

use ITRocks\Framework\Component\Menu;
use ITRocks\Framework\Component\Menu\Item;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Updater\Application_Updater;

/**
 * Updatable feature class list, taken from the menu only
 */
class Menu_Update extends Update
{

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		Application_Updater::get()->addUpdatable($this);
	}

	//---------------------------------------------------------------------------------------- update
	/**
	 * Update feature classes cache, using the menu items list
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $last_time integer
	 */
	public function update($last_time)
	{
		[$class_names, $feature_classes, $write] = $this->updateInit();
		foreach (array_keys(Menu::get()->configuration_items) as $menu_item_link) {
			$item       = new Item();
			$item->link = $menu_item_link;
			$class_name = $item->linkClass();
			$this->updateClassName($class_name, $class_names, $feature_classes, $write);
		}
		$this->writeFeatureClasses($feature_classes, $write);
	}

}
