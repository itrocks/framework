<?php
namespace ITRocks\Framework\Plugin\Installable;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Builder\Assembled;
use ITRocks\Framework\Configuration\File\Builder\Replaced;
use ITRocks\Framework\Configuration\File\Config;
use ITRocks\Framework\Configuration\File\Menu;
use ITRocks\Framework\Configuration\File\Source;
use ITRocks\Framework\Plugin;
use ITRocks\Framework\Plugin\Installable;
use ITRocks\Framework\Updater\Application_Updater;

/**
 * Installer
 */
class Installer
{

	//---------------------------------------------------------------------------------------- $files
	/**
	 * Modified files
	 *
	 * @var File[] File[string $file_name]
	 */
	protected $files = [];

	//--------------------------------------------------------------------------------------- addMenu
	/**
	 * Add blocks and items configuration to the menu.php configuration file
	 *
	 * @param $blocks array string $item_caption[string $block_title][string $item_link]
	 */
	public function addMenu(array $blocks)
	{
		$file = $this->openFile(Menu::class);
		$file->addBlocks($blocks);
	}

	//------------------------------------------------------------------------------------- addPlugin
	/**
	 * Add the a Activable / Configurable / Registrable plugin into the config.php configuration file
	 *
	 * @param $priority_value string @values Priority::const
	 * @param $plugin_name    string
	 * @param $configuration  mixed
	 */
	public function addPlugin($priority_value, $plugin_name, $configuration = null)
	{
		$file = $this->openFile(Config::class);
		$file->addPlugin($priority_value, $plugin_name, $configuration);
	}

	//------------------------------------------------------------------------------------ addToClass
	/**
	 * Add interfaces and traits to the base class, into the builder.php configuration file
	 *
	 * @param $base_class_name         string
	 * @param $added_interfaces_traits string|string[]
	 */
	public function addToClass($base_class_name, $added_interfaces_traits)
	{
		$file  = $this->openFile(File\Builder::class);
		$built = $file->search($base_class_name);
		if (!$built) {
			$file->add($base_class_name, $added_interfaces_traits);
		}
		elseif ($built instanceof Assembled) {
			$built->add($added_interfaces_traits, $file);
		}
		elseif ($built instanceof Replaced) {
			/** @var $file Source PhpStorm is bugged : with meta, it should be found */
			$file = $this->openFile(Source::class, $built->replacement);
			$file->add($added_interfaces_traits);
		}
		else {
			trigger_error(
				'Found class ' . $base_class_name . ' should be Assembled or Replaced', E_USER_ERROR
			);
		}
	}

	//------------------------------------------------------------------------------------- dependsOn
	/**
	 * The plugin depends on all these plugins : install them before me
	 *
	 * @param $plugin_class_names string|string[] A list of needed plugin classes
	 */
	public function dependsOn($plugin_class_names)
	{
		if (!is_array($plugin_class_names)) {
			$plugin_class_names = [$plugin_class_names];
		}
		foreach ($plugin_class_names as $plugin_class_name) {
			$this->install($plugin_class_name);
		}
	}

	//--------------------------------------------------------------------------------------- install
	/**
	 * @param $plugin Installable|string plugin object or class name
	 */
	public function install($plugin)
	{
		if (is_string($plugin)) {
			$plugin = is_a($plugin, Installable::class, true)
				? Builder::create($plugin)
				: Builder::create(Implicit::class, [$plugin]);
		}
		$plugin->install($this);
	}

	//-------------------------------------------------------------------------------------- openFile
	/**
	 * Open the configuration file (if not already opened), and return it
	 *
	 * @param $file_class string
	 * @param $file_name  null
	 * @return File
	 */
	protected function openFile($file_class, $file_name = null)
	{
		if (!$file_name) {
			/** @noinspection PhpUndefinedMethodInspection File::defaultFileName */
			$file_name = $file_class::defaultFileName();
		}
		if (!isset($this->files[$file_name])) {
			/** @var $file File */
			$file = new $file_class($file_name);
			$file->read();
			$this->files[$file_name] = $file;

		}
		return $this->files[$file_name];
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

	//------------------------------------------------------------------------------------ renameMenu
	/**
	 * Rename a menu block
	 *
	 * @param $old_menu string
	 * @param $new_menu string
	 */
	public function renameMenu($old_menu, $new_menu)
	{

	}

	/**
	 * Remove the installed plugin from the config.php configuration file
	 *
	 * - If the plugin is Installable and is not $this : launch the uninstall procedure for it
	 * - If the plugin is not Installable or is $this : only remove it from the config.php
	 *
	 * @param $plugin Plugin
	 */
	/*
	public function removePlugin(Plugin $plugin)
	{
		foreach (array_keys((new ReflectionClass(Priority::class))->getConstants()) as $constant_name) {
			$this->file->remove(['Priority::' . $constant_name], [get_class($plugin) . '::class']);
		}
	}
	*/

	//------------------------------------------------------------------------------------- saveFiles
	/**
	 * Save opened files
	 */
	public function saveFiles()
	{
		if ($this->files) {
			foreach ($this->files as $file) {
				$file->write();
			}
			touch(Application_Updater::UPDATE_FILE);
		}
	}

}
