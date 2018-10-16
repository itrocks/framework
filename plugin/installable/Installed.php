<?php
namespace ITRocks\Framework\Plugin\Installable;

use ITRocks\Framework\Configuration\File;

/**
 * An installed plugin
 *
 * @store_name installed_plugins
 */
class Installed extends File\Installed
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
		return static::addProperties(['plugin_class_name' => $plugin_class_name]);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * @param $plugin_class_name string
	 * @return static
	 */
	public function remove($plugin_class_name)
	{
		return static::removeProperties(['plugin_class_name' => $plugin_class_name]);
	}

}
