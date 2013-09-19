<?php
namespace SAF\Framework;

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
		/** @var $import Import */
		$import = $parameters->getMainObject('SAF\Framework\Import');
		$parameters = $parameters->getObjects();
		$form = (new File_Builder_Post_Files())->appendToForm($form, $files);
		//echo "<h2>FILE</h2><pre>" . print_r($form, true) . "</pre>";
		foreach ($form as $file) {
			if ($file instanceof File) {
				//echo date("H:i:s") . "<br>";
				//$timer = new Execution_Timer();
				$excel = Excel_File::fileToArray($file->temporary_file_name);
				//echo "Excel_File::fileToArray duration = " . $timer->end() . "<br>";
				//$timer = new Execution_Timer();
				$worksheet_number = 0;
				foreach ($excel as $temporary_file_name => $worksheet) {
					$import->worksheets[] = new Import_Worksheet(
						$worksheet_number ++,
						Import_Settings_Builder::buildArray($worksheet),
						new File($temporary_file_name),
						new Import_Preview($worksheet)
					);
					//echo "<h2>IMPORT OBJECTS</h2>";
					//(new Import_Array())->importArray($worksheet);
				}
				//echo "importArray duration = " . $timer->end() . "<br>";
			}
		}
		//echo "<h2>IMPORT PREVIEW / SETTINGS</h2><pre>" . print_r($import, true) . "</pre>";
		return View::run($parameters, $form, $files, 'SAF\Framework\Import', "preview");
	}

}
