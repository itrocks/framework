<?php
namespace ITRocks\Framework\RAD\Feature;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Plugin\Installable;
use ITRocks\Framework\Plugin\Installable\Implicit;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\RAD\Feature;

/**
 * The feature installer plugin enables to maintain the status of the features when plugins are
 * installed
 */
class Installer implements Registerable
{

	//-------------------------------------------------------------------------------- installFeature
	/**
	 * Called after an Installable plugin install() method is called : tells the feature is installed
	 *
	 * @param $object Installable
	 */
	public function installFeature(Installable $object)
	{
		$plugin     = $object;
		$class_name = ($plugin instanceof Implicit) ? $plugin->class->name : get_class($plugin);
		if ($feature = Dao::searchOne(['plugin_class_name' => $class_name], Feature::class)) {
			$feature->status = Status::INSTALLED;
			Dao::write($feature, Dao::only('status'));
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->afterMethod([Installable::class, 'install'], [$this, 'installFeature']);
	}

}
