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
	public function addMenu(array $blocks)
	{
		echo PRE . 'Add menu ' . print_r($blocks, true) . _PRE;
	}

	//------------------------------------------------------------------------------------- addPlugin
	/**
	 * Add the a Activable / Configurable / Registrable plugin into the config.php configuration file
	 *
	 * @param $plugin Plugin
	 */
	public function addPlugin(Plugin $plugin)
	{
		echo PRE . 'Add plugin ' . get_class($plugin) . ' to config.php' . _PRE;
	}

	//------------------------------------------------------------------------------- addToBuiltClass
	/**
	 * Add interfaces and traits to the base class, into the builder.php configuration file
	 *
	 * @param $base_class_name         string
	 * @param $added_interfaces_traits string[]
	 */
	public function addToBuiltClass($base_class_name, array $added_interfaces_traits)
	{
		echo PRE . 'Build class ' . $base_class_name . ' => ' . print_r($added_interfaces_traits, true) . _PRE;
	}

	//------------------------------------------------------------------------------------- dependsOn
	/**
	 * The plugin depends on all these plugins : install them before me
	 *
	 * @param $plugin_class_names string[] A list of needed plugin classes
	 */
	public function dependsOn(array $plugin_class_names)
	{
		echo PRE . 'Depends on ' . print_r($plugin_class_names, true) . ' plugins' . _PRE;
	}

	//-------------------------------------------------------------------------- removeFromBuiltClass
	/**
	 * Remove interfaces and traits from the base class, into the builder.php configuration file
	 *
	 * @param $base_class_name           string
	 * @param $removed_interfaces_traits string[]
	 */
	public function removeFromBuiltClass($base_class_name, array $removed_interfaces_traits)
	{

	}

	//------------------------------------------------------------------------------------ removeMenu
	/**
	 * Remove blocks and items configuration from the menu.php configuration file
	 *
	 * @param $blocks array
	 */
	public function removeMenu(array $blocks)
	{

	}

	//---------------------------------------------------------------------------------- removePlugin
	/**
	 * Remove the installed plugin from the config.php configuration file
	 *
	 * - If the plugin is Installable and is not $this : launch the uninstall procedure for it
	 * - If the plugin is not Installable or is $this : only remove it from the config.php
	 *
	 * @param $plugin Plugin
	 */
	public function removePlugin(Plugin $plugin)
	{

	}

}
