<?php
namespace ITRocks\Framework\Report\Dashboard;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Setting;
use ITRocks\Framework\View;

/**
 * Appends an indicator to the current dashboard, only knowing its source list settings
 */
class Append_Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'append';

	//------------------------------------------------------------------------------------------- run
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$setting = $parameters->getObject(Setting::class);
		if (!$setting) {
			return 'NOK';
		}
		/** @noinspection PhpUnhandledExceptionInspection class */
		$indicator = Builder::create(Indicator::class, [$setting]);
		$indicator->placeOnGrid();
		Dao::write($indicator);
		Main::$current->redirect(View::link($indicator->dashboard));
		return View::run($parameters->getObjects(), $form, $files, Indicator::class, static::FEATURE);
	}

}
