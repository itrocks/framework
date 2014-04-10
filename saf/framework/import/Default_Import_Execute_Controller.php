<?php
namespace SAF\Framework\Import;

use SAF\Framework\Controller\Default_Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Dao\File\Session_File\Files;
use SAF\Framework\Session;
use SAF\Framework\View;

/**
 * Import execution controller
 */
class Default_Import_Execute_Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files, $class_name)
	{
		set_time_limit(900);
		$import = Import_Builder_Form::build(
			$form, Session::current()->get(Files::class)->files
		);
		$import->class_name = $class_name;
		$parameters->getMainObject($import);
		$parameters = $parameters->getObjects();
		foreach ($import->worksheets as $worksheet) {
			$array = $worksheet->file->getCsvContent();
			$import_array = new Import_Array($worksheet->settings, $import->class_name);
			$import_array->importArray($array);
		}
		return View::run($parameters, $form, $files, $class_name, 'importDone');
	}

}
