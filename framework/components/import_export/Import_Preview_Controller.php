<?php
namespace SAF\Framework;

use \StdClass;

/**
 * Import preview controller
 */
class Import_Preview_Controller implements Feature_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $class_name string object or class name
	 * @return Button[]
	 */
	protected function getGeneralButtons($class_name)
	{
		return array(
			"save" => new Button(
				"Save", View::link($class_name, "preview"),
				"custom_save", array(Color::of("blue"), "#main", ".submit")
			),
			"delete" => new Button(
				"Delete", View::link($class_name, "preview", null, array("delete_import" => true)),
				"custom_delete", array(Color::of("red"), "#main", ".submit")
			)
		);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		// convert form files to worksheets and session files
		if ($files) {
			/** @var $import Import */
			$import = $parameters->getMainObject('SAF\Framework\Import');
			$form = (new File_Builder_Post_Files())->appendToForm($form, $files);
			foreach ($form as $file) {
				if ($file instanceof File) {
					if (!isset($session_files)) {
						$session_files = new Session_Files();
					}
					$excel = Excel_File::fileToArray($file->temporary_file_name);
					$worksheet_number = 0;
					foreach ($excel as $temporary_file_name => $worksheet) {
						$import->worksheets[] = new Import_Worksheet(
							$worksheet_number ++,
							Import_Settings_Builder::buildArray($worksheet),
							$csv_file = new File($temporary_file_name)
						);
						$session_files->files[] = $csv_file;
					}
					// only one file once
					break;
				}
			}
			if (isset($session_files)) {
				Session::current()->set($session_files);
			}
		}
		// convert from form and session files to worksheets
		else {
			$parameters->unshift($import = Import_Builder_Form::build(
				$form, Session::current()->get('SAF\Framework\Session_Files')->files
			));
		}
		// prepare parameters
		$parameters = $parameters->getObjects();
		$general_buttons = $this->getGeneralButtons('SAF\Framework\Import');
		foreach ($import->worksheets as $worksheet_number => $worksheet) {
			// apply controller parameters
			$worksheet->settings = Custom_Settings_Controller::applyParametersToCustomSettings(
				$worksheet->settings, array_merge($form, $parameters)
			) ?: $worksheet->settings;
			// get general buttons and customized import settings
			$customized_import_settings = Import_Settings::getCustomSettings($worksheet->settings);
			$worksheet_general_buttons = $general_buttons;
			if (!isset($customized_import_settings[$worksheet->settings->name])) {
				unset($worksheet_general_buttons["delete"]);
			}
			$parameters["custom"][$worksheet_number] = new StdClass();
			$parameters["custom"][$worksheet_number]->customized_lists = $customized_import_settings;
			$parameters["custom"][$worksheet_number]->general_buttons = $worksheet_general_buttons;
		}
		// view
		return View::run($parameters, $form, $files, 'SAF\Framework\Import', "preview");
	}

}
