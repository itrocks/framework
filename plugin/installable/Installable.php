<?php
namespace ITRocks\Framework\Plugin;

use ITRocks\Framework\Plugin\Installable\Installer;

/**
 * An installable plugin
 */
interface Installable
{

	//------------------------------------------------------------------------------------ __toString
	/**
	 * Returns the short description of the installable plugin
	 *
	 * It is the feature caption exactly how it will be displayed to the user
	 *
	 * @return string
	 */
	public function __toString() : string;

	//--------------------------------------------------------------------------------------- install
	/**
	 * This code is called when the plugin is installed by the user
	 *
	 * Use the $installer parameter to install the components of your plugin.
	 *
	 * @param $installer Installer
	 */
	public function install(Installer $installer);

}
