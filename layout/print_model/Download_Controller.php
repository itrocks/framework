<?php
namespace ITRocks\Framework\Layout\Print_Model;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Feature\Message;
use ITRocks\Framework\Layout\Print_Model;
use ITRocks\Framework\Layout\Print_Model\Remote\Client;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tools\Set;
use ITRocks\Framework\View;

/**
 * Print model download controller
 */
class Download_Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'download';

	//------------------------------------------------------------------------------------------- run
	public function run(Parameters $parameters, array $form, array $files)
	{
		$print_models = Client::get()->download($form);
		Main::$current->redirect(View::link(Print_Model::class, Feature::F_LIST), Target::MAIN);
		return Message::display(
			new Set(Print_Model::class, $print_models),
			Loc::tr(
				':count print models were downloaded',
				Loc::replace(['count' => count($print_models)])
			)
		);
	}

}
