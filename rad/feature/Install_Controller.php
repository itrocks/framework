<?php
namespace ITRocks\Framework\RAD\Feature;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\RAD\Feature;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;

/**
 * User end-feature install controller
 */
class Install_Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'install';

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		$feature = $parameters->getMainObject(Feature::class);
		if ($parameters->getRawParameter('confirm')) {
			$parameters->set('installed', $feature->install());
			$parameters->set(Template::TEMPLATE, 'installed');
		}
		else {
			$dependencies = $feature->willInstall($feature->plugin_class_name);
			foreach ($dependencies as $key => $dependency) {
				if ($dependency->status === Status::INSTALLED) {
					unset($dependencies[$key]);
				}
				else {
					$dependency->title = Loc::tr($dependency->title);
				}
			}
			uasort($dependencies, function(Feature $f1, Feature $f2) {
				return strcmp($f1->title, $f2->title);
			});
			$parameters->set(
				'confirm_link', View::link($feature, static::FEATURE, null, ['confirm' => true])
			);
			$parameters->set('dependencies', $dependencies);
		}
		return View::run($parameters->getObjects(), $form, $files, Feature::class, static::FEATURE);
	}

}
