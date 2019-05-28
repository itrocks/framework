<?php
namespace ITRocks\Framework\Print_Model;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Layout\Print_Model;
use ITRocks\Framework\Plugin\Installable;
use ITRocks\Framework\Plugin\Installable\Installer;
use ITRocks\Framework\View;

/**
 * Print models
 */
class Plugin implements Installable
{

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'Print models';
	}

	//--------------------------------------------------------------------------------------- install
	/**
	 * @noinspection PhpDocMissingThrowsInspection ::class is known
	 * @param $installer Installer
	 */
	public function install(Installer $installer)
	{
		/** @noinspection PhpUnhandledExceptionInspection ::class is known */
		$installer->addMenu(
			['Administration' => [View::link(Print_Model::class, Feature::F_LIST) => 'Print models']]
		);
	}

}
