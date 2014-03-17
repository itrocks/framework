<?php
namespace SAF\Framework\RAD;

use SAF\Framework\Controller;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\View;

class Plugins_Controller implements Controller
{

	//---------------------------------------------------------------------------- runDatabaseToFiles
	/**
	 * @param $parameters   Controller_Parameters
	 * @param $form         array
	 * @param $files        array
	 * @return mixed
	 */
	public function runDatabaseToFiles(Controller_Parameters $parameters, $form, $files)
	{
		$maintainer = new Plugins_Maintainer();
		$maintainer->databaseToFiles();
		return View::run($parameters->getObjects(), $form, $files, 'Plugins', 'databaseToFiles');
	}

	//---------------------------------------------------------------------------- runFilesToDatabase
	/**
	 * @param $parameters   Controller_Parameters
	 * @param $form         array
	 * @param $files        array
	 * @return mixed
	 */
	public function runFilesToDatabase(Controller_Parameters $parameters, $form, $files)
	{
		$maintainer = new Plugins_Maintainer();
		$maintainer->filesToDatabase();
		return View::run($parameters->getObjects(), $form, $files, 'Plugins', 'filesToDatabase');
	}

}
