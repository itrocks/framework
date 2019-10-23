<?php
namespace ITRocks\Framework\Plugin\Installable\Installed;

use ITRocks\Framework\Plugin\Installable\Installed;

/**
 * An installed plugin (into config.php)
 *
 * @store_name installed_plugins
 */
class Plugin extends Installed
{

	//---------------------------------------------------------------------------- $plugin_class_name
	/**
	 * @var string
	 */
	public $plugin_class_name;

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $plugin_class_name string
	 * @return static
	 */
	public function add($plugin_class_name)
	{
		return $this->addProperties(['plugin_class_name' => $plugin_class_name]);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * @param $plugin_class_name string
	 * @return static
	 */
	public function remove($plugin_class_name)
	{
		return $this->removeProperties(['plugin_class_name' => $plugin_class_name]);
	}

}
