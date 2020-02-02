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
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Plugin;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Installable;
use ITRocks\Framework\RAD\Feature;
use ITRocks\Framework\RAD\Feature\Bridge;
use ITRocks\Framework\RAD\Feature\Status;
use ITRocks\Framework\Reflection\Annotation\Class_\Extends_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Feature_Build_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Feature_Exclude_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Feature_Include_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Feature_Menu_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
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

	//----------------------------------------------------------------------- $modified_built_classes
	/**
	 * @var string[]
	 */
	protected $modified_built_classes = [];

	//---------------------------------------------------------------------------- $plugin_class_name
	/**
	 * @var string
	 */
	public $plugin_class_name;

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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $plugin_class_name string|Plugin
	 * @param $configuration     mixed
	 * @param $priority_value    string If forced priority only @values Priority::const
	 */
	public function addPlugin($plugin_class_name, $configuration = null, $priority_value = null)
	{
		if (!is_string($plugin_class_name)) {
			$plugin_class_name = get_class($plugin_class_name);
		}
		if (is_a($plugin_class_name, Plugin::class, true)) {
			if (!$priority_value) {
				/** @noinspection PhpUnhandledExceptionInspection */
				$plugin_class   = new Reflection_Class($plugin_class_name);
				$priority_value = $plugin_class->getAnnotation('priority')->value;
			}
			$file = $this->openFile(Config::class);
			$file->addPlugin($priority_value, $plugin_class_name, $configuration);
			(new Installed\Plugin($this->plugin_class_name))->add($plugin_class_name);
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
		$this->modified_built_classes[$base_class_name] = $base_class_name;
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
			if (!beginsWith($added_interface_trait, AT)) {
				(new Installed\Builder($this->plugin_class_name))
					->add($base_class_name, $added_interface_trait);
				Post::get()->willInstallProperties($base_class_name, $added_interface_trait);
			}
		}
	}

	//------------------------------------------------------------------------------ buildAnnotations
	/**
	 * Build dynamic annotations
	 */
	public function buildAnnotations()
	{
		$exhaustive_class       = new Exhaustive_Class($this->files);
		$modified_built_classes = $this->modified_built_classes;
		foreach ($modified_built_classes as $class_name) {
			foreach (Dependency::extendsUse($class_name) as $descendent_class_name) {
				if (
					isset($exhaustive_class->assembly[$descendent_class_name])
					&& !isset($modified_built_classes[$descendent_class_name])
				) {
					$modified_built_classes[$descendent_class_name] = $descendent_class_name;
				}
			}
		}
		foreach ($modified_built_classes as $base_class_name) {
			foreach ($exhaustive_class->classAnnotations($base_class_name) as $name => $raw_value) {
				$this->addToClass($base_class_name, AT . $name . SP . $raw_value);
			}
		}
	}

	//--------------------------------------------------------------------------------------- install
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $plugin Installable|string plugin to install
	 */
	public function install($plugin)
	{
		Dao::begin();
		$plugin_class_name         = is_string($plugin) ? $plugin : get_class($plugin);
		$stacked_plugin_class_name = $this->plugin_class_name;
		$this->plugin_class_name   = $plugin_class_name;
		/** @noinspection PhpUnhandledExceptionInspection plugin class name must be valid */
		$plugin_class = new Reflection_Class($plugin_class_name);

		foreach (Feature_Exclude_Annotation::allOf($plugin_class) as $feature_exclude) {
			$this->uninstall(Builder::current()->sourceClassName($feature_exclude->value));
		}
		foreach (Feature_Include_Annotation::allOf($plugin_class) as $feature_include) {
			$dependency_class_name = Builder::current()->sourceClassName($feature_include->value);
			$this->install($dependency_class_name);
			(new Installed\Dependency($plugin_class_name))->add($dependency_class_name);
		}
		foreach ($plugin_class->getAnnotations('feature_install') as $feature_install) {
			/** @var $feature_install Method_Annotation */
			$feature_install->call(
				$plugin_class->isAbstract() ? $plugin_class_name : $plugin_class->newInstance()
			);
		}
		// menu items : only the highest level feature menu for each /Class/Path/featureName is kept
		$menu_items = [];
		foreach (Feature_Menu_Annotation::allOf($plugin_class) as $feature_menu) {
			$menu_items[$feature_menu->value] = [
				$feature_menu->block_caption => [$feature_menu->value => $feature_menu->item_caption]
			];
		}
		foreach ($menu_items as $menu) {
			$this->addMenu($menu);
		}
		foreach (Feature_Build_Annotation::allOf($plugin_class) as $build_annotation) {
			$class_name = reset($build_annotation->value);
			if (class_exists($class_name)) {
				$slice = 1;
			}
			else {
				/** @noinspection PhpUnhandledExceptionInspection Must be valid */
				$class_name = $plugin_class->isClass()
					? $plugin_class->name
					: reset(Extends_Annotation::of(new Reflection_Class($class_name))->value);
				$slice = 0;
			}
			foreach (array_slice($build_annotation->value, $slice) as $interface_trait_name) {
				$this->addToClass($class_name, $interface_trait_name);
			}
		}

		$installable = $this->pluginObject($plugin);
		$installable->install($this);

		if ($feature = Dao::searchOne(['plugin_class_name' => $plugin_class_name], Feature::class)) {
			$feature->status = Status::INSTALLED;
			Dao::write($feature, Dao::only('status'));
		}

		(new Bridge($this))->automaticInstallFor($plugin_class_name);

		$this->plugin_class_name = $stacked_plugin_class_name;
		Dao::commit();
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
	 * @noinspection PhpDocMissingThrowsInspection
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

	//------------------------------------------------------------------------------- removeDependent
	/**
	 * @param $feature Feature
	 */
	protected function removeDependent(Feature $feature)
	{
		$this->uninstall($feature->plugin_class_name);
		(new Installed\Dependency($feature->plugin_class_name))->remove($this->plugin_class_name);
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
		$this->modified_built_classes[$base_class_name] = $base_class_name;
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
			$built->remove($removed_interfaces_traits, $file);
			if (!$built->components) {
				$file->remove($built);
			}
		}
		elseif ($built instanceof Replaced) {
			/** @var $file Source PhpStorm is bugged : with meta, it should be found */
			$file = $this->openFile(Source::class, Names::classToFilePath($built->replacement));
			$file->remove($removed_interfaces_traits);
		}
		elseif ($built) {
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
	 * @return boolean true if the plugin had no dependency anymore, false if was dependent
	 */
	public function removePlugin($plugin_class_name)
	{
		$removed = (new Installed\Plugin($this->plugin_class_name))->remove($plugin_class_name);
		if (!$removed || !$removed->features) {
			$file = $this->openFile(Config::class);
			$file->removePlugin($plugin_class_name);
			if ($plugin_class_name !== $this->plugin_class_name) {
				$this->uninstall($plugin_class_name);
			}
			return true;
		}
		return false;
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
			$this->buildAnnotations();
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
	 * @param $plugin_class_name Installable|string plugin object or class name
	 */
	public function uninstall($plugin_class_name)
	{
		Dao::begin();
		$stacked_plugin_class_name = $this->plugin_class_name;
		$this->plugin_class_name   = $plugin_class_name;

		// remove all dependencies that need this plugin
		foreach ($this->willUninstall($plugin_class_name, false) as $feature) {
			$this->removeDependent($feature);
		}

		$installed_search = ['features.plugin_class_name' => $plugin_class_name];

		// unset useless dependencies
		/** @var $installed_dependencies Installed\Dependency[] */
		$installed_dependencies = Dao::search($installed_search, Installed\Dependency::class);
		foreach ($installed_dependencies as $installed_dependency) {
			Dao::delete($installed_dependency);
		}

		// remove all plugins installed by this plugin
		/** @var $installed_plugins Installed\Plugin[] */
		$installed_plugins = Dao::search($installed_search, Installed\Plugin::class);
		foreach ($installed_plugins as $installed_plugin) {
			$this->removePlugin($installed_plugin->plugin_class_name);
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

		if ($feature = Dao::searchOne(['plugin_class_name' => $plugin_class_name], Feature::class)) {
			$feature->status = Status::AVAILABLE;
			Dao::write($feature, Dao::only('status'));
		}

		(new Bridge($this))->automaticUninstallFor($plugin_class_name);

		$this->plugin_class_name = $stacked_plugin_class_name;
		Dao::commit();
	}

	//----------------------------------------------------------------------------------- willInstall
	/**
	 * Returns the list of plugins that will be installed if you install this one
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $plugin_class_name string
	 * @param $recurse           boolean
	 * @return Feature[]
	 */
	public function willInstall($plugin_class_name, $recurse = true)
	{
		$features = [];
		/** @noinspection PhpUnhandledExceptionInspection must be valid */
		$includes = Feature_Include_Annotation::allOf(new Reflection_Class($plugin_class_name));
		foreach ($includes as $include) {
			$feature_class_name = Builder::current()->sourceClassName($include->value);
			$feature = Dao::searchOne(['plugin_class_name' => $feature_class_name], Feature::class);
			if (isset($features[$feature->plugin_class_name])) {
				continue;
			}
			$features = array_merge(
				$features,
				[$feature->plugin_class_name => $feature],
				$recurse ? $this->willInstall($feature->plugin_class_name) : []
			);
		}
		return $features;
	}

	//--------------------------------------------------------------------------------- willUninstall
	/**
	 * Returns the list of plugins that will be uninstalled if you uninstall this one
	 *
	 * @param $plugin_class_name string
	 * @param $recurse           boolean
	 * @return Feature[]
	 */
	public function willUninstall($plugin_class_name, $recurse = true)
	{
		$features = [];
		$dependency_search = ['dependency.plugin_class_name' => $plugin_class_name];
		/** @var $dependents Installed\Dependency[] */
		$dependents = Dao::search($dependency_search, Installed\Dependency::class);
		foreach ($dependents as $dependent) {
			foreach ($dependent->features as $feature) {
				if (isset($features[$feature->plugin_class_name])) {
					continue;
				}
				$features = array_merge(
					$features,
					[$feature->plugin_class_name => $feature],
					$recurse ? $this->willUninstall($feature->plugin_class_name) : []
				);
			}
		}
		return $features;
	}

}
