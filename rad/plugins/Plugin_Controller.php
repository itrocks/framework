<?php
namespace ITRocks\Framework\RAD\Plugins;

use ITRocks\Framework;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\View;

/**
 * RAD plugins controller
 */
class Plugin_Controller implements Framework\Controller
{

	//---------------------------------------------------------------------------- runDatabaseToFiles
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function runDatabaseToFiles(Parameters $parameters, array $form, array $files)
	{
		$maintainer = new Maintainer();
		$maintainer->databaseToFiles();
		return View::run($parameters->getObjects(), $form, $files, Plugin::class, 'databaseToFiles');
	}

	//---------------------------------------------------------------------------- runFilesToDatabase
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function runFilesToDatabase(Parameters $parameters, array $form, array $files)
	{
		$maintainer = new Maintainer();
		$maintainer->filesToDatabase();
		return View::run($parameters->getObjects(), $form, $files, Plugin::class, 'filesToDatabase');
	}

}
