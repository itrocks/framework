<?php
namespace ITRocks\Framework\RAD;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Plugin\Installable\Installer;
use ITRocks\Framework\RAD\Feature\Module;
use ITRocks\Framework\RAD\Feature\Status;
use ITRocks\Framework\Reflection\Attribute\Class_\Display_Order;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Reflection\Attribute\Property\Multiline;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Namespaces;

/**
 * Final user installable feature
 *
 * @after_read initModule
 * @list title, status
 * @representative title
 */
#[Display_Order('title', 'module', 'description', 'status', 'tags'), Store('rad_features')]
class Feature
{

	//----------------------------------------------------------------------- $application_class_name
	#[User(User::INVISIBLE)]
	public string $application_class_name;

	//--------------------------------------------------------------------------------------- $bridge
	#[User(User::INVISIBLE)]
	public bool $bridge;

	//---------------------------------------------------------------------------------- $description
	/**
	 * @max_length 64000
	 * @translate common
	 */
	#[Multiline]
	public string $description;

	//--------------------------------------------------------------------------------------- $module
	public ?Module $module;

	//---------------------------------------------------------------------------- $plugin_class_name
	#[User(User::INVISIBLE)]
	public string $plugin_class_name;

	//--------------------------------------------------------------------------------------- $status
	#[User(User::READONLY)]
	#[Values(Status::class)]
	public string $status = Status::AVAILABLE;

	//----------------------------------------------------------------------------------------- $tags
	/** @var Tag[] */
	public array $tags;

	//---------------------------------------------------------------------------------------- $title
	/** @translate common */
	#[Mandatory]
	public string $title;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $title       string|null Feature title
	 * @param $description string|null Feature complete description
	 */
	public function __construct(string $title = null, string $description = null)
	{
		if (isset($title))       $this->title       = $title;
		if (isset($description)) $this->description = $description;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->title ? Loc::tr($this->title) : '';
	}

	//------------------------------------------------------------------------------------ initModule
	public function initModule() : void
	{
		if ($this->module) {
			return;
		}
		$module_name = ucfirst(Names::classToDisplay(Namespaces::project($this->plugin_class_name)));
		$module      = Dao::searchOne(['name' => $module_name], Module::class);
		if (!$module) {
			/** @noinspection PhpUnhandledExceptionInspection class */
			$module       = Builder::create(Module::class);
			$module->name = $module_name;
			Dao::write($module);
		}
		$this->module = $module;
		Dao::write($this, Dao::only('module'));
	}

	//--------------------------------------------------------------------------------------- install
	/**
	 * Installs this feature, ie install the matching Installable plugin
	 *
	 * @return boolean true if the feature was correctly installed
	 */
	public function install() : bool
	{
		Dao::begin();
		$installer = new Installer();
		$installer->install($this->plugin_class_name);
		Dao::commit();
		return true;
	}

	//------------------------------------------------------------------------------------- uninstall
	/** @return boolean true if the feature was correctly uninstalled */
	public function uninstall() : bool
	{
		Dao::begin();
		$installer = new Installer();
		$installer->uninstall($this->plugin_class_name);
		Dao::commit();
		return true;
	}

	//----------------------------------------------------------------------------------- willInstall
	/**
	 * Returns the list of plugins that will be installed if you install this one
	 *
	 * @param $recurse boolean
	 * @return Feature[]
	 */
	public function willInstall(bool $recurse = true) : array
	{
		$installer                    = new Installer();
		$installer->plugin_class_name = $this->plugin_class_name;
		$will_install                 = $installer->willInstall($this->plugin_class_name, $recurse);
		unset($will_install[$this->plugin_class_name]);
		return $will_install;
	}

	//--------------------------------------------------------------------------------- willUninstall
	/**
	 * Returns the list of plugins that will be uninstalled if you uninstall this one
	 *
	 * @param $recurse boolean
	 * @return Feature[]
	 */
	public function willUninstall(bool $recurse = true) : array
	{
		$installer                    = new Installer();
		$installer->plugin_class_name = $this->plugin_class_name;
		$will_uninstall               = $installer->willUninstall($this->plugin_class_name, $recurse);
		unset($will_uninstall[$this->plugin_class_name]);
		return $will_uninstall;
	}

}
