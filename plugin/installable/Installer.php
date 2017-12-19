<?php
namespace ITRocks\Framework\Plugin\Installable;

use ITRocks\Framework\Plugin;

/**
 * Installer
 */
class Installer
{

	//--------------------------------------------------------------------------------------- addMenu
	/**
	 * Add blocks and items configuration to the menu.php configuration file
	 *
	 * @param $blocks array
	 */
	public function addMenu($blocks)
	{
		echo PRE . 'Add menu ' . print_r($blocks, true) . _PRE;
	}

	//------------------------------------------------------------------------------------ buildClass
	/**
	 * Add interfaces and traits to the base class, into the builder.php configuration file
	 *
	 * @param $base_class_name         string
	 * @param $added_interfaces_traits string[]
	 */
	public function buildClass($base_class_name, array $added_interfaces_traits)
	{
		echo PRE . 'Build class ' . $base_class_name . ' => ' . print_r($added_interfaces_traits, true) . _PRE;
	}

	//--------------------------------------------------------------------------------- installPlugin
	/**
	 * Add the plugin into the config.php configuration file
	 *
	 * @param $plugin Plugin
	 */
	public function installPlugin(Plugin $plugin)
	{

	}

	//------------------------------------------------------------------------------------ removeMenu
	/**
	 * Remove blocks and items configuration from the menu.php configuration file
	 *
	 * @param $blocks array
	 */
	public function removeMenu($blocks)
	{

	}

	//---------------------------------------------------------------------------------- unBuildClass
	/**
	 * Remove interfaces and traits from the base class, into the builder.php configuration file
	 *
	 * @param $base_class_name           string
	 * @param $removed_interfaces_traits string[]
	 */
	public function unBuildClass($base_class_name, array $removed_interfaces_traits)
	{

	}

	//------------------------------------------------------------------------------- uninstallPlugin
	/**
	 * Remove the plugin from the config.php configuration file
	 *
	 * @param $plugin Plugin
	 */
	public function uninstallPlugin(Plugin $plugin)
	{

	}

}
