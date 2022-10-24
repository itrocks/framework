<?php
namespace ITRocks\Framework\RAD\Feature;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\RAD\Feature;
use ITRocks\Framework\View;

/**
 * Features maintain controller
 *
 * @example http://it.rocks/studio/ITRocks/Framework/RAD/Feature/maintain
 */
class Maintain_Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'maintain';

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$maintainer = new Maintainer();
		$maintainer->installableToFeaturesUpdate();
		return View::run($parameters->getObjects(), $form, $files, Feature::class, static::FEATURE);
	}

}
