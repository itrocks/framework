<?php
namespace ITRocks\Framework\RAD;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Plugin\Installable\Installer;
use ITRocks\Framework\RAD\Feature\Status;

/**
 * Final user installable feature
 *
 * @business
 * @display_order title, description, status, tags
 * @list title, status
 * @representative title
 * @store_name rad_features
 */
class Feature
{

	//----------------------------------------------------------------------- $application_class_name
	/**
	 * @user invisible
	 * @var string
	 */
	public $application_class_name;

	//--------------------------------------------------------------------------------------- $bridge
	/**
	 * @user invisible
	 * @var boolean
	 */
	public $bridge;

	//---------------------------------------------------------------------------------- $description
	/**
	 * @max_length 64000
	 * @multiline
	 * @translate common
	 * @var string
	 */
	public $description;

	//---------------------------------------------------------------------------- $plugin_class_name
	/**
	 * @user invisible
	 * @var string
	 */
	public $plugin_class_name;

	//--------------------------------------------------------------------------------------- $status
	/**
	 * @user readonly
	 * @values Status::const
	 * @var string
	 */
	public $status = Status::AVAILABLE;

	//----------------------------------------------------------------------------------------- $tags
	/**
	 * @link Map
	 * @var Tag[]
	 */
	public $tags;

	//---------------------------------------------------------------------------------------- $title
	/**
	 * @mandatory
	 * @translate common
	 * @var string
	 */
	public $title;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $title       string Feature title
	 * @param $description string Feature complete description
	 */
	public function __construct($title = null, $description = null)
	{
		if (isset($title))       $this->title       = $title;
		if (isset($description)) $this->description = $description;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->title ? Loc::tr($this->title) : '';
	}

	//--------------------------------------------------------------------------------------- install
	/**
	 * Installs this feature, ie install the matching Installable plugin
	 *
	 * @return boolean true if the feature was correctly installed
	 */
	public function install()
	{
		Dao::begin();
		$installer = new Installer();
		$installer->install($this->plugin_class_name);
		$installer->saveFiles();
		Dao::commit();
		return true;
	}

	//------------------------------------------------------------------------------------- uninstall
	/**
	 * @return boolean true if the feature was correctly uninstalled
	 */
	public function uninstall()
	{
		Dao::begin();
		$installer = new Installer();
		$installer->uninstall($this->plugin_class_name);
		$installer->saveFiles();
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
	public function willInstall($recurse = true)
	{
		$installer    = new Installer();
		$will_install = $installer->willInstall($this->plugin_class_name, $recurse);
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
	public function willUninstall($recurse = true)
	{
		$installer      = new Installer();
		$will_uninstall = $installer->willUninstall($this->plugin_class_name, $recurse);
		unset($will_uninstall[$this->plugin_class_name]);
		return $will_uninstall;
	}

}
