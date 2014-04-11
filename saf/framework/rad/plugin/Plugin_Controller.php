<?php
namespace SAF\Framework\RAD\Plugin;

use SAF\Framework;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\RAD\Plugin;
use SAF\Framework\View;

/**
 * RAD plugins controller
 */
class Plugin_Controller implements Framework\Controller
{

	//---------------------------------------------------------------------------- runDatabaseToFiles
	/**
	 * @param $parameters   Parameters
	 * @param $form         array
	 * @param $files        array
	 * @return mixed
	 */
	public function runDatabaseToFiles(Parameters $parameters, $form, $files)
	{
		$maintainer = new Maintainer();
		$maintainer->databaseToFiles();
		return View::run($parameters->getObjects(), $form, $files, Plugin::class, 'databaseToFiles');
	}

	//---------------------------------------------------------------------------- runFilesToDatabase
	/**
	 * @param $parameters   Parameters
	 * @param $form         array
	 * @param $files        array
	 * @return mixed
	 */
	public function runFilesToDatabase(Parameters $parameters, $form, $files)
	{
		$maintainer = new Maintainer();
		$maintainer->filesToDatabase();
		return View::run($parameters->getObjects(), $form, $files, Plugin::class, 'filesToDatabase');
	}

}
