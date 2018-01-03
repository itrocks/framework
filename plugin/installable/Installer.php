<?php
namespace ITRocks\Framework\Plugin\Installable;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Plugin;
use ITRocks\Framework\Plugin\Installable;
use ITRocks\Framework\Plugin\Priority;
use ITRocks\Framework\Session;
use ReflectionClass;

/**
 * Installer
 */
class Installer
{

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @var File
	 */
	protected $file;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->file = Builder::create(File::class, [Session::current()->configuration_file_name]);
		$this->file->read();
	}

	//--------------------------------------------------------------------------------------- addMenu
	/**
	 * Add blocks and items configuration to the menu.php configuration file
	 *
	 * @param $blocks array
	 */
	public function addMenu(array $blocks)
	{
		echo PRE . 'Add menu ' . print_r($blocks, true) . _PRE;
		$this->file->add(['Priority::NORMAL', 'Menu::class'], $blocks);
	}

	//------------------------------------------------------------------------------------- addPlugin
	/**
	 * Add the a Activable / Configurable / Registrable plugin into the config.php configuration file
	 *
	 * @param $plugin   Plugin
	 * @param $priority string @values Priority::const
	 */
	public function addPlugin($priority = Priority::NORMAL, Plugin $plugin)
	{
		echo PRE . 'Add plugin ' . get_class($plugin) . ' to config.php' . _PRE;
		$this->file->add(['Priority::' . strtoupper($priority)], [get_class($plugin) . '::class']);
	}

	//------------------------------------------------------------------------------------ addToClass
	/**
	 * Add interfaces and traits to the base class, into the builder.php configuration file
	 *
	 * @param $base_class_name         string
	 * @param $added_interfaces_traits string[]
	 */
	public function addToClass($base_class_name, array $added_interfaces_traits)
	{
		echo PRE . 'Base class ' . $base_class_name . ' => ' . print_r($added_interfaces_traits, true) . _PRE;
		$this->file->add(
			['Priority::CORE', 'Builder::class', $base_class_name],
			$added_interfaces_traits
		);
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
		foreach ($plugin_class_names as $plugin_class_name) {
			if (is_a($plugin_class_name, Installable::class)) {
				/** @var $plugin Installable */
				$plugin = Builder::create($plugin_class_name);
				$plugin->install($this);
			}
			else {
				trigger_error('Plugin ' . $plugin_class_name . ' is not Installable', E_USER_ERROR);
			}
		}
	}

	//------------------------------------------------------------------------------- removeFromClass
	/**
	 * Remove interfaces and traits from the base class, into the builder.php configuration file
	 *
	 * @param $base_class_name           string
	 * @param $removed_interfaces_traits string[]
	 */
	public function removeFromClass($base_class_name, array $removed_interfaces_traits)
	{
		$this->file->remove(
			['Priority::CORE', 'Builder::class', $base_class_name],
			$removed_interfaces_traits
		);
	}

	//------------------------------------------------------------------------------------ removeMenu
	/**
	 * Remove blocks and items configuration from the menu.php configuration file
	 *
	 * @param $blocks array
	 */
	public function removeMenu(array $blocks)
	{
		$this->file->remove(['Priority::NORMAL', 'Menu::class'], $blocks);
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
		foreach (array_keys((new ReflectionClass(Priority::class))->getConstants()) as $constant_name) {
			$this->file->remove(['Priority::' . $constant_name], [get_class($plugin) . '::class']);
		}
	}

}
