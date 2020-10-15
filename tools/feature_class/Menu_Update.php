<?php
namespace ITRocks\Framework\Tools\Feature_Class;

use Exception;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Component\Menu;
use ITRocks\Framework\Component\Menu\Item;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Layout\Print_Model;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Updater\Application_Updater;
use ITRocks\Framework\View;

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
		/** @var $class_names     string[] */
		/** @var $feature_classes Keep[] */
		/** @var $write           Keep[] */
		[$class_names, $feature_classes, $write] = $this->updateInit();
		$links             = array_keys(Menu::get()->configuration_items);
		try {
			$print_model_links = array_keys(Dao::readAll(Print_Model::class, Dao::key('class_name')));
		}
		catch (Exception $exception) {
			$print_model_links = [];
		}
		foreach ($print_model_links as $print_model_link) {
			$links[] = View::link($print_model_link, Feature::F_PRINT);
		}
		foreach ($links as $menu_item_link) {
			/** @noinspection PhpUnhandledExceptionInspection class */
			$item       = Builder::create(Item::class);
			$item->link = $menu_item_link;
			$class_name = $item->linkClass();
			$this->updateClassName($class_name, $class_names, $feature_classes, $write);
		}
		$this->writeFeatureClasses($feature_classes, $write);
	}

}
