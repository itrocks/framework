<?php
namespace ITRocks\Framework\Objects\Note\Summary_Edit;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Objects\Note;
use ITRocks\Framework\View;

/**
 * Note summary-add controller
 */
class Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'summaryEdit';

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		$object = $parameters->firstObject();
		$note   = $parameters->getMainObject(Note::class);
		if ($note->object) {
			$parameters->set('close_link', View::link($note, 'summaryOutput'));
		}
		else {
			$note->object = $object;
			$parameters->set('close_link', '/ITRocks/Framework/blank');
		}
		return View::run($parameters->getObjects(), $form, $files, Note::class, static::FEATURE);
	}

}
