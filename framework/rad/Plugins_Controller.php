<?php
namespace SAF\Framework\RAD;

use SAF\Framework\Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\View;

/**
 * RAD plugins controller
 */
class Plugins_Controller implements Controller
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
		$maintainer = new Plugins_Maintainer();
		$maintainer->databaseToFiles();
		return View::run($parameters->getObjects(), $form, $files, 'Plugins', 'databaseToFiles');
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
		$maintainer = new Plugins_Maintainer();
		$maintainer->filesToDatabase();
		return View::run($parameters->getObjects(), $form, $files, 'Plugins', 'filesToDatabase');
	}

}
