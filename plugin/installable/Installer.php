<?php
namespace ITRocks\Framework\Plugin\Installable;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Builder\Assembled;
use ITRocks\Framework\Configuration\File\Builder\Replaced;
use ITRocks\Framework\Configuration\File\Config;
use ITRocks\Framework\Configuration\File\Menu;
use ITRocks\Framework\Configuration\File\Source;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Installable;
use ITRocks\Framework\Tools\Names;
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

	//---------------------------------------------------------------------------- $plugin_class_name
	/**
	 * @var string
	 */
	protected $plugin_class_name;

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
		(new Installed\Plugin($this->plugin_class_name))->add($plugin_name);
		if ($this->plugin_class_name !== $plugin_name) {
			$this->install($plugin_name);
		}
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
			$file = $this->openFile(Source::class, Names::classToFilePath($built->replacement));
			$file->add($added_interfaces_traits);
		}
		else {
			trigger_error(
				'Found class ' . $base_class_name . ' should be Assembled or Replaced', E_USER_ERROR
			);
		}
		if (!is_array($added_interfaces_traits)) {
			$added_interfaces_traits = [$added_interfaces_traits];
		}
		foreach ($added_interfaces_traits as $added_interface_trait) {
			(new Installed\Builder($this->plugin_class_name))
				->add($base_class_name, $added_interface_trait);
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $plugin Installable|string plugin to install
	 */
	public function install($plugin)
	{
		$stacked_plugin_class_name = $this->plugin_class_name;

		$this->plugin_class_name = is_string($plugin) ? $plugin : get_class($plugin);
		$installable             = $this->pluginObject($plugin);
		$installable->install($this);

		$this->plugin_class_name = $stacked_plugin_class_name;
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

	//---------------------------------------------------------------------------------- pluginObject
	/**
	 * @param $plugin Installable|string
	 * @return Installable
	 */
	protected function pluginObject($plugin)
	{
		if (is_string($plugin)) {
			/** @noinspection PhpUnhandledExceptionInspection $plugin must be a valid class name */
			$plugin = is_a($plugin, Installable::class, true)
				? Builder::create($plugin, is_a($plugin, Configurable::class, true) ? [[]] : [])
				: Builder::create(Implicit::class, [$plugin]);
		}
		return $plugin;
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
		// mark interfaces / traits as removed, without removing them
		foreach ($removed_interfaces_traits as $removal_key => $removed_interface_trait) {
			$installed = (new Installed\Builder($this->plugin_class_name))
				->remove($base_class_name, $removed_interface_trait);
			// do not remove the entry from the built class if it is still used by other features
			if ($installed && $installed->features) {
				unset($removed_interfaces_traits[$removal_key]);
			}
		}
		// remove all unused interfaces / traits
		$file  = $this->openFile(File\Builder::class);
		$built = $file->search($base_class_name);
		if ($built instanceof Assembled) {
			$built->remove($removed_interfaces_traits);
			if (!$built->components) {
				$file->remove($built);
			}
		}
		elseif ($built instanceof Replaced) {
			/** @var $file Source PhpStorm is bugged : with meta, it should be found */
			$file = $this->openFile(Source::class, Names::classToFilePath($built->replacement));
			$file->remove($removed_interfaces_traits);
		}
		else {
			trigger_error(
				'Found class ' . $base_class_name . ' should be Assembled or Replaced', E_USER_ERROR
			);
		}
	}

	//------------------------------------------------------------------------------------ removeMenu
	/**
	 * Remove blocks and items configuration from the menu.php configuration file
	 *
	 * @param $blocks array string $item_caption[string $block_title][string $item_link]
	 */
	public function removeMenu(array $blocks)
	{
		$file = $this->openFile(Menu::class);
		$file->removeBlocks($blocks);
	}

	//---------------------------------------------------------------------------------- removePlugin
	/**
	 * Remove the installed plugin from the config.php configuration file
	 *
	 * - If the plugin is Installable and is not $this : launch the uninstall procedure for it
	 * - If the plugin is not Installable or is $this : only remove it from the config.php
	 *
	 * @param $plugin_class_name string
	 */
	public function removePlugin($plugin_class_name)
	{
		$removed = (new Installed\Plugin($this->plugin_class_name))->remove($plugin_class_name);
		if (!$removed || !$removed->features) {
			$file = $this->openFile(Config::class);
			$file->removePlugin($plugin_class_name);
		}
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
		// TODO rename menu, and update installed menus structure too
	}

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

	//------------------------------------------------------------------------------------- uninstall
	/**
	 * Uninstall a plugin, and all plugins that depend on it
	 *
	 * Notice : Developers should beware that the user has well been informed of the full list of
	 * dependency features he will lost on uninstalling this feature.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $plugin_class_name Installable|string plugin object or class name
	 */
	public function uninstall($plugin_class_name)
	{
		$stacked_plugin_class_name = $this->plugin_class_name;
		$this->plugin_class_name   = $plugin_class_name;

		$installed_search = ['features.plugin_class_name' => $plugin_class_name];

		// remove all plugins that depend on this plugin
		/** @var $installed_menus Installed[] */
		$installed_plugins = Dao::search($installed_search, Installed\Plugin::class);
		foreach ($installed_plugins as $installed_plugin) {
			if ($installed_plugin->plugin_class_name !== $plugin_class_name) {
				$this->uninstall($installed_plugin->plugin_class_name);
			}
		}

		// remove interfaces / traits from built classes
		/** @var $installed_builds Installed\Builder[] */
		$installed_builds = Dao::search($installed_search, Installed\Builder::class);
		foreach ($installed_builds as $installed_build) {
			$this->removeFromClass($installed_build->base_class, [$installed_build->added_class]);
		}

		// remove menus that depend on this plugin (if they depend on this plugin only)
		/** @var $installed_menus Installed\Menu[] */
		$installed_menus = Dao::search($installed_search, Installed\Menu::class);
		foreach ($installed_menus as $installed_menu) {
			$this->removeMenu([
				$installed_menu->block_title => [
					$installed_menu->item_link => $installed_menu->item_caption
				]
			]);
		}

		// remove the plugin itself
		$this->plugin_class_name = $stacked_plugin_class_name;
		$this->removePlugin($plugin_class_name);
	}

}
