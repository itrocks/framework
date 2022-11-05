<?php
namespace ITRocks\Framework\RAD\Feature;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\RAD\Feature;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;

/**
 * User end-feature uninstall controller
 */
class Uninstall_Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'uninstall';

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$feature = $parameters->getMainObject(Feature::class);
		if ($parameters->getRawParameter('confirm')) {
			$parameters->set('uninstalled', $feature->uninstall());
			$parameters->set(Template::TEMPLATE, 'uninstalled');
		}
		else {
			$dependents = $feature->willUninstall($feature->plugin_class_name);
			foreach ($dependents as $dependent) {
				$dependent->title = Loc::tr($dependent->title);
			}
			uasort($dependents, function(Feature $f1, Feature $f2) : int {
				return strcmp($f1->title, $f2->title);
			});
			$parameters->set(
				'confirm_link', View::link($feature, static::FEATURE, null, ['confirm' => true])
			);
			$parameters->set('dependents', $dependents);
		}
		return View::run($parameters->getObjects(), $form, $files, Feature::class, static::FEATURE);
	}

}
