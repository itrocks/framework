<?php
namespace ITRocks\Framework\Report\Dashboard\Indicator;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Feature\Message;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\View;

/**
 * Dashboard indicator move controller
 */
class Move_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return string
	 */
	public function run(Parameters $parameters, array $form, array $files) : string
	{
		$indicator = $parameters->getMainObject();
		if ($indicator) {
			$indicator->moveTo($parameters->getRawParameter('x'), $parameters->getRawParameter('y'));
			Main::$current->redirect(View::link($indicator->dashboard, Feature::F_OUTPUT), Target::MAIN);
		}
		return Message::display($indicator, Loc::tr('Indicator moved'));
	}

}
