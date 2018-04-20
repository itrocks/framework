<?php
namespace ITRocks\Framework\RAD;

use ITRocks\Framework\Plugin\Installable;
use ITRocks\Framework\Plugin\Installable\Implicit;
use ITRocks\Framework\Plugin\Installable\Installer;
use ITRocks\Framework\RAD\Feature\Status;
use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * Final user installable feature
 *
 * @display_order title, description, tags, status
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

	//---------------------------------------------------------------------------------- $description
	/**
	 * @max_length 64000
	 * @multiline
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
		return strval($this->title);
	}

	//--------------------------------------------------------------------------------------- install
	/**
	 * Installs this feature, ie install the matching Installable plugin
	 */
	public function install()
	{
		$installer = new Installer();
		$this->plugin()->install($installer);
		$installer->saveFiles();
	}

	//---------------------------------------------------------------------------------------- plugin
	/**
	 * Instantiates an Installable plugin that patches $plugin_class_name
	 *
	 * @return Installable|object
	 */
	public function plugin()
	{
		if (is_a($this->plugin_class_name, Installable::class, true)) {
			/** @var $plugin Installable */
			return (new Reflection_Class($this->plugin_class_name))->newInstance();
		}
		return new Implicit($this->plugin_class_name);
	}

}
