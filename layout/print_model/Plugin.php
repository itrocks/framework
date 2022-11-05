<?php
namespace ITRocks\Framework\Layout\Print_Model;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Layout\Print_Model;
use ITRocks\Framework\Plugin\Installable;
use ITRocks\Framework\Plugin\Installable\Installer;
use ITRocks\Framework\View;

/**
 * Customizable print models
 */
class Plugin implements Installable
{

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return 'Customizable print models';
	}

	//--------------------------------------------------------------------------------------- install
	/**
	 * @param $installer Installer
	 */
	public function install(Installer $installer) : void
	{
		$installer->addMenu(
			['Administration' => [View::link(Print_Model::class, Feature::F_LIST) => 'Print models']]
		);
	}

}
