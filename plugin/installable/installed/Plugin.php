<?php
namespace ITRocks\Framework\Plugin\Installable\Installed;

use ITRocks\Framework\Plugin\Installable\Installed;
use ITRocks\Framework\Reflection\Attribute\Class_\Store_Name;

/**
 * An installed plugin (into config.php)
 */
#[Store_Name('installed_plugins')]
class Plugin extends Installed
{

	//---------------------------------------------------------------------------- $plugin_class_name
	/**
	 * @var string
	 */
	public string $plugin_class_name;

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $plugin_class_name string
	 * @return static
	 */
	public function add(string $plugin_class_name) : static
	{
		return $this->addProperties(['plugin_class_name' => $plugin_class_name]);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * @param $plugin_class_name string
	 * @return ?static
	 */
	public function remove(string $plugin_class_name) : ?static
	{
		return $this->removeProperties(['plugin_class_name' => $plugin_class_name]);
	}

}
