<?php
namespace ITRocks\Framework\Layout\Model;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Layout\Model;
use ITRocks\Framework\Plugin\Installable;
use ITRocks\Framework\Plugin\Installable\Installer;
use ITRocks\Framework\View;

/**
 * Layout models
 */
class Plugin implements Installable
{

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'Layout models';
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
			['Administration' => [View::link(Model::class, Feature::F_LIST) => 'Print models']]
		);
	}

}
