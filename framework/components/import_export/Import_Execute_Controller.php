<?php
namespace SAF\Framework;

/**
 * Import execution controller
 */
class Import_Execute_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		//Mysql_Logger::getInstance()->continue = true;
		//Mysql_Logger::getInstance()->display_log = true;

		set_time_limit(900);
		$parameters = $parameters->getObjects();
		$import = Import_Builder_Form::build(
			$form, Session::current()->get('SAF\Framework\Session_Files')->files
		);
		foreach ($import->worksheets as $worksheet) {
			$array = $worksheet->file->getCsvContent();
			(new Import_Array($worksheet->settings))->importArray($array);
		}
		return View::run($parameters, $form, $files, 'SAF\Framework\Import', "done");
	}

}
