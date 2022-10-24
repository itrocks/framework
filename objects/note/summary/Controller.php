<?php
namespace ITRocks\Framework\Objects\Note\Summary;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Objects\Note;
use ITRocks\Framework\View;

/**
 * Notes summary controller
 */
class Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'summary';

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$object = $parameters->firstObject();
		$parameters->set('object', $object);
		$parameters->set('notes', Dao::search(['object' => $object], Note::class, Dao::sort()));
		return View::run($parameters->getObjects(), $form, $files, Note::class, static::FEATURE);
	}

}
