<?php
namespace ITRocks\Framework\Locale\Translation\Data;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Locale\Translation\Data;
use ITRocks\Framework\View;
use ITRocks\Framework\Widget\Button;
use ITRocks\Framework\Widget\Edit;

/**
 * Data translation form : translate one property value into all available languages in one form
 */
class Form_Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'form';

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $object     object
	 * @param $parameters array
	 * @return Button[]
	 */
	public function getGeneralButtons($object, array $parameters)
	{
		return (new Edit\Controller)->getGeneralButtons($object, $parameters);
	}

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
		$object        = $parameters->getMainObject();
		$property_name = $parameters->getRawParameter('property') ?: $parameters->shiftUnnamed();
		$data_set      = new Set($object, $property_name);
		$parameters->unshift($data_set);
		$parameters = $parameters->getObjects();
		$parameters['general_buttons'] = $this->getGeneralButtons($data_set, $parameters);
		return View::run($parameters, $form, $files, Data::class, static::FEATURE);
	}

}
