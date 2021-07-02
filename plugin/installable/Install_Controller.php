<?php
namespace ITRocks\Framework\Plugin\Installable;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Plugin\Installable;

/**
 * Plugin install controller
 *
 * Call this for your plugin to get it installed
 * Returns 'Installed' if done, or an error message if there has been a problem
 */
class Install_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return string 'Installed'
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		/** @var $plugin Installable */
		$plugin    = $parameters->getMainObject();
		$installer = new Installer();
		$installer->install($plugin);
		return 'Installed';
	}

}
