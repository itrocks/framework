<?php
namespace SAF\Framework;

/*
require_once dirname(__FILE__) . "/../../vendor/excel_reader/oleread.inc";
require_once dirname(__FILE__) . "/../../vendor/excel_reader/reader.php";

use \Spreadsheet_Excel_Reader;
*/

/**
 * Import preview controller
 */
class Import_Preview_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$parameters->getMainObject('SAF\Framework\Import');
		$parameters = $parameters->getObjects();
		$form = (new File_Builder_Post_Files())->appendToForm($form, $files);
		foreach ($form as $file) {
			if ($file instanceof File) {
				// $excel = Excel_File::createFromFile($file->temporary_file_name);
				// $excel = new Spreadsheet_Excel_Reader(); // $excel->read($file->temporary_file_name);
				echo date("H:i:s") . "<br>";
				$timer = new Execution_Timer();
				$excel = Excel_File::fileToArray($file->temporary_file_name);
				echo "Excel_File::fileToArray duration = " . $timer->end() . "<br>";
				$timer = new Execution_Timer();
				foreach ($excel as $worksheet) {
					(new Import_Array())->importArray($worksheet);
				}
				echo "importArray duration = " . $timer->end() . "<br>";
			}
		}
		return View::run($parameters, $form, $files, 'SAF\Framework\Import', "preview");
	}

}
