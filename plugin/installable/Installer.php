<?php
namespace ITRocks\Framework\Plugin\Installable;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Builder\Assembled;
use ITRocks\Framework\Configuration\File\Builder\Replaced;
use ITRocks\Framework\Configuration\File\Config;
use ITRocks\Framework\Configuration\File\Local_Access;
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
use ITRocks\Framework\Reflection\Annotation\Class_\Feature_Annotate_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Feature_Build_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Feature_Exclude_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Feature_Include_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Feature_Install_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Feature_Menu_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Feature_Plugin_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Feature_Uninstall_Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_\Extends_;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Updater\Application_Updater;
use ITRocks\Framework\Updater\Will_Call;

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
	protected array $files = [];

	//----------------------------------------------------------------------- $modified_built_classes
	/**
	 * @var string[]
	 */
	protected array $modified_built_classes = [];

	//---------------------------------------------------------------------------- $plugin_class_name
	/**
	 * @var string
	 */
	public string $plugin_class_name = '';

	//-------------------------------------------------------------------------------- addLocalAccess
	/**
	 * @param $local_access string
	 */
	public function addLocalAccess(string $local_access) : void
	{
		$file = $this->openFile(Local_Access::class);
		$file->add($local_access);
	}

	//--------------------------------------------------------------------------------------- addMenu
	/**
	 * Add blocks and items configuration to the menu.php configuration file
	 *
	 * @param $blocks array string $item_caption[string $block_title][string $item_link]
	 */
	public function addMenu(array $blocks) : void
	{
		$file = $this->openFile(Menu::class);
		$file->addBlocks($blocks);
	}

	//------------------------------------------------------------------------------------- addPlugin
	/**
	 * Add an Activable / Configurable / Registrable plugin into the config.php configuration file
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $plugin_class_name Plugin|string
	 * @param $configuration     mixed
	 * @param $priority_value    string If forced priority only @values Priority::const
	 */
	public function addPlugin(
		Plugin|string $plugin_class_name, mixed $configuration = null, string $priority_value = ''
	) : void
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
	 * @param $base_class_name         string          Class name (real class) to be 'improved'
	 * @param $added_interfaces_traits string|string[] Interface, trait, or class annotation
	 */
	public function addToClass(string $base_class_name, array|string $added_interfaces_traits) : void
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
			if (!str_starts_with($added_interface_trait, AT)) {
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
	public function buildAnnotations() : void
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

	//----------------------------------------------------------------------------------- hasMenuLink
	/**
	 * @param $link string
	 * @return boolean
	 */
	public function hasMenuLink(string $link) : bool
	{
		return $this->openFile(Menu::class)->hasLink($link);
	}

	//--------------------------------------------------------------------------------------- install
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $plugin   Installable|string plugin to install
	 * @param $features Feature[]
	 */
	public function install(Installable|string $plugin, array $features = []) : void
	{
		$plugin_class_name         = is_string($plugin) ? $plugin : get_class($plugin);
		$stacked_plugin_class_name = $this->plugin_class_name;
		$this->plugin_class_name   = $plugin_class_name;
		/** @noinspection PhpUnhandledExceptionInspection plugin class name must be valid */
		$plugin_class = new Reflection_Class($plugin_class_name);
		if (isset($features[$plugin_class_name])) {
			return;
		}
		$features[$plugin_class_name] = true;

		Dao::begin();
		$should_save_files = false;
		foreach ($plugin_class->getAnnotations('feature_local_access') as $feature_local_access) {
			$this->addLocalAccess($feature_local_access->value);
			$should_save_files = true;
		}
		if ($should_save_files) {
			$this->saveFiles();
		}
		foreach (Feature_Exclude_Annotation::allOf($plugin_class) as $feature_exclude) {
			$this->uninstall(Builder::current()->sourceClassName($feature_exclude->value));
		}
		foreach (Feature_Include_Annotation::allOf($plugin_class) as $feature_include) {
			$dependency_class_name = Builder::current()->sourceClassName($feature_include->value);
			$this->install($dependency_class_name, $features);
			(new Installed\Dependency($plugin_class_name))->add($dependency_class_name);
		}
		foreach (Feature_Install_Annotation::allOf($plugin_class) as $feature_install) {
			if ($feature_install->delay) {
				Will_Call::add(explode('::', $feature_install->value), $feature_install->delay);
			}
			else {
				/** @noinspection PhpUnhandledExceptionInspection must be valid */
				$feature_install->call(
					$plugin_class->isAbstract() ? $plugin_class_name : $plugin_class->newInstance(),
					[__METHOD__, 'install']
				);
			}
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
			if (class_exists($class_name) || $build_annotation->build_first) {
				$slice = 1;
			}
			else {
				/** @noinspection PhpUnhandledExceptionInspection Must be valid */
				$class                = new Reflection_Class($class_name);
				$interface_trait_name = Builder::current()->sourceClassName(reset(
					Extends_::oneNotOf($class, Extends_::STRICT)->extends
				));
				$class_name = $plugin_class->isClass() ? $plugin_class->name : $interface_trait_name;
				$slice = 0;
			}
			foreach (array_slice($build_annotation->value, $slice) as $interface_trait_name) {
				$interface_trait_name = Builder::current()->sourceClassName($interface_trait_name);
				$this->addToClass(Builder::current()->sourceClassName($class_name), $interface_trait_name);
			}
		}
		foreach (Feature_Annotate_Annotation::allOf($plugin_class) as $annotate_annotation) {
			$class_name = Builder::current()->sourceClassName(reset($annotate_annotation->value));
			$this->addToClass($class_name, $annotate_annotation->annotation);
		}
		foreach (Feature_Plugin_Annotation::allOf($plugin_class) as $plugin_annotation) {
			foreach ($plugin_annotation->values() as $feature_plugin_class_name) {
				$this->addPlugin($feature_plugin_class_name);
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
		$this->saveFiles();
	}

	//-------------------------------------------------------------------------------------- openFile
	/**
	 * Open the configuration file (if not already opened), and return it
	 *
	 * @param $file_class class-string<T>
	 * @param $file_name  string|null
	 * @return T
	 * @template T
	 */
	protected function openFile(string $file_class, string $file_name = null) : object
	{
		if (!$file_name) {
			/** @see File::defaultFileName() */
			/** @noinspection PhpUndefinedMethodInspection class-string */
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
	protected function pluginObject(Installable|string $plugin) : Installable
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
	 * @param $feature  Feature
	 * @param $features Feature[]
	 */
	protected function removeDependent(Feature $feature, array $features) : void
	{
		$this->uninstall($feature->plugin_class_name, $features);
		(new Installed\Dependency($feature->plugin_class_name))->remove($this->plugin_class_name);
	}

	//------------------------------------------------------------------------------- removeFromClass
	/**
	 * Remove interfaces and traits from the base class, into the builder.php configuration file
	 *
	 * @param $base_class_name           string
	 * @param $removed_interfaces_traits string[]
	 */
	public function removeFromClass(string $base_class_name, array $removed_interfaces_traits) : void
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

	//----------------------------------------------------------------------------- removeLocalAccess
	/**
	 * @param $local_access string
	 */
	public function removeLocalAccess(string $local_access) : void
	{
		$file = $this->openFile(Local_Access::class);
		$file->remove($local_access);
	}

	//------------------------------------------------------------------------------------ removeMenu
	/**
	 * Remove blocks and items configuration from the menu.php configuration file
	 *
	 * @param $blocks array string $item_caption[string $block_title][string $item_link]
	 */
	public function removeMenu(array $blocks) : void
	{
		$file = $this->openFile(Menu::class);
		$file->removeBlocks($blocks);
	}

	//---------------------------------------------------------------------------------- removePlugin
	/**
	 * Remove the installed plugin from the config.php configuration file
	 *
	 * - If the plugin is Installable and is not $this : launch uninstall procedure for it
	 * - If the plugin is not Installable or is $this : only remove it from the config.php
	 *
	 * @param $plugin_class_name string
	 * @return boolean true if the plugin had no dependency anymore, false if was dependent
	 */
	public function removePlugin(string $plugin_class_name) : bool
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
	public function renameMenu(string $old_menu, string $new_menu) : void
	{
		// TODO rename menu, and update installed menus structure too
	}

	//------------------------------------------------------------------------------------- saveFiles
	/**
	 * Save opened files
	 */
	public function saveFiles() : void
	{
		if (!$this->files) {
			return;
		}
		$this->buildAnnotations();
		foreach ($this->files as $file) {
			$file->write();
		}
		$this->files = [];
		touch(Application_Updater::UPDATE_FILE);
	}

	//------------------------------------------------------------------------------------- uninstall
	/**
	 * Uninstall a plugin, and all plugins that depend on it
	 *
	 * Notice : Developers should beware that the user has well been informed of the full list of
	 * dependency features he will lose on uninstalling this feature.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $plugin_class_name Installable|string plugin object or class name
	 * @param $features          Feature[]
	 */
	public function uninstall(Installable|string $plugin_class_name, array $features = []) : void
	{
		if (isset($features[$plugin_class_name])) {
			return;
		}
		$features[$plugin_class_name] = true;
		Dao::begin();
		$stacked_plugin_class_name = $this->plugin_class_name;
		$this->plugin_class_name   = $plugin_class_name;
		/** @noinspection PhpUnhandledExceptionInspection plugin class name must be valid */
		$plugin_class = new Reflection_Class($plugin_class_name);

		// remove all dependencies that need this plugin
		foreach ($this->willUninstall($plugin_class_name, false) as $feature) {
			if (!isset($features[$feature->plugin_class_name])) {
				$this->removeDependent($feature, $features);
			}
		}

		foreach (Feature_Uninstall_Annotation::allOf($plugin_class) as $feature_install) {
			/** @noinspection PhpUnhandledExceptionInspection must be valid */
			$feature_install->call(
				$plugin_class->isAbstract() ? $plugin_class_name : $plugin_class->newInstance(),
				[__METHOD__, 'uninstall']
			);
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

		// remove local access installed by this plugin
		foreach ($plugin_class->getAnnotations('feature_local_access') as $feature_local_access) {
			$this->removeLocalAccess($feature_local_access->value);
		}

		if ($feature = Dao::searchOne(['plugin_class_name' => $plugin_class_name], Feature::class)) {
			$feature->status = Status::AVAILABLE;
			Dao::write($feature, Dao::only('status'));
		}

		(new Bridge($this))->automaticUninstallFor($plugin_class_name);

		$this->plugin_class_name = $stacked_plugin_class_name;
		Dao::commit();
		$this->saveFiles();
	}

	//----------------------------------------------------------------------------------- willInstall
	/**
	 * Returns the list of plugins that will be installed if you install this one
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $plugin_class_name string
	 * @param $recurse           boolean
	 * @param $features          Feature[] key is Feature::$plugin_class_name only if recurse
	 * @return Feature[]
	 */
	public function willInstall(string $plugin_class_name, bool $recurse = true, array $features = [])
		: array
	{
		/** @noinspection PhpUnhandledExceptionInspection must be valid */
		$includes = Feature_Include_Annotation::allOf(new Reflection_Class($plugin_class_name));
		foreach ($includes as $include) {
			$feature_class_name = Builder::current()->sourceClassName($include->value);
			/** @var $feature Feature */
			$feature = Dao::searchOne(['plugin_class_name' => $feature_class_name], Feature::class);
			if (isset($features[$feature->plugin_class_name])) {
				continue;
			}
			$features[$feature->plugin_class_name] = $feature;
			if ($recurse) {
				$backup_class_name       = $this->plugin_class_name;
				$this->plugin_class_name = $feature->plugin_class_name;
				$features = $this->willInstall($feature->plugin_class_name, true, $features);
				$this->plugin_class_name = $backup_class_name;
			}
		}
		return $features;
	}

	//--------------------------------------------------------------------------------- willUninstall
	/**
	 * Returns the list of plugins that will be uninstalled if you uninstall this one
	 *
	 * @param $plugin_class_name string
	 * @param $recurse           boolean
	 * @param $features          Feature[] key is Feature::$plugin_class_name only if recurse
	 * @return Feature[]
	 */
	public function willUninstall(
		string $plugin_class_name, bool $recurse = true, array $features = []
	) : array
	{
		$dependency_search = ['dependency.plugin_class_name' => $plugin_class_name];
		/** @var $dependents Installed\Dependency[] */
		$dependents = Dao::search($dependency_search, Installed\Dependency::class);
		foreach ($dependents as $dependent) {
			foreach ($dependent->features as $feature) {
				if (isset($features[$feature->plugin_class_name])) {
					continue;
				}
				$features[$feature->plugin_class_name] = $feature;
				if ($recurse) {
					$backup_class_name       = $this->plugin_class_name;
					$this->plugin_class_name = $feature->plugin_class_name;
					$features = $this->willUninstall($feature->plugin_class_name, true, $features);
					$this->plugin_class_name = $backup_class_name;
				}
			}
		}
		return $features;
	}

}
