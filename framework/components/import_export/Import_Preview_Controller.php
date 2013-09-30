<?php
namespace SAF\Framework;

use StdClass;

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
				"Delete", View::link($class_name, "preview", null, array("delete_name" => true)),
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
						$import_worksheet = new Import_Worksheet(
							$worksheet_number ++,
							Import_Settings_Builder::buildArray($worksheet),
							$csv_file = new File($temporary_file_name)
						);
						$session_files->files[] = $csv_file;
						$import->worksheets[] = $import_worksheet;
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
			$files = Session::current()->get('SAF\Framework\Session_Files')->files;
			$parameters->unshift($import = Import_Builder_Form::build($form, $files));
		}
		// prepare parameters
		$parameters = $parameters->getObjects();
		$general_buttons = $this->getGeneralButtons('SAF\Framework\Import');
		if (
			isset($parameters["constant_remove"])
			&& (strtoupper($parameters["constant_remove"][0]) === $parameters["constant_remove"][0])
		) {
			$parameters["constant_remove"] = rParse($parameters["constant_remove"], ".");
		}
		foreach ($import->worksheets as $worksheet) {
			// apply controller parameters
			if (
				isset($parameters["constant_add"])
				&& isset($worksheet->settings->classes[$parameters["constant_add"]])
			) {
				$worksheet->settings->classes[$parameters["constant_add"]]->addConstant();
			}
			if (
				isset($parameters["constant_remove"])
				&& isset($worksheet->settings->classes[lLastParse($parameters["constant_remove"], ".", 1, false)])
			) {
				$worksheet->settings->classes[lLastParse($parameters["constant_remove"], ".", 1, false)]
					->removeConstant(rLastParse($parameters["constant_remove"], ".", 1, true));
			}
			Custom_Settings_Controller::applyParametersToCustomSettings(
				$worksheet->settings, array_merge($form, $parameters)
			);
		}
		// recover empty Import_Settings (after loading empty one)
		/** @var $files File[] */
		$files = Session::current()->get('SAF\Framework\Session_Files')->files;
		foreach ($import->worksheets as $worksheet_number => $worksheet) {
			if (empty($worksheet->settings->classes)) {
				$file = $files[$worksheet_number];
				$array = $file->getCsvContent();
				$import->worksheets[$worksheet_number] = new Import_Worksheet(
					$worksheet_number, Import_Settings_Builder::buildArray($array), $file
				);
			}
		}
		// get general buttons and customized import settings
		foreach ($import->worksheets as $worksheet_number => $worksheet) {
			$customized_import_settings = $worksheet->settings->getCustomSettings();
			$worksheet_general_buttons = $general_buttons;
			if (!isset($customized_import_settings[$worksheet->settings->name])) {
				unset($worksheet_general_buttons["delete"]);
			}
			$parameters["custom"][$worksheet_number] = new StdClass();
			$parameters["custom"][$worksheet_number]->customized_lists = $customized_import_settings;
			$parameters["custom"][$worksheet_number]->general_buttons = $worksheet_general_buttons;
			$parameters["custom"][$worksheet_number]->settings = $worksheet->settings;
			$parameters["custom"][$worksheet_number]->aliases_property = Import_Array::getPropertiesAlias(
				$worksheet->settings->getClassName()
			);
			$parameters["custom"][$worksheet_number]->properties_alias = array_flip(
				$parameters["custom"][$worksheet_number]->aliases_property
			);
		}
		// view
		return View::run($parameters, $form, $files, 'SAF\Framework\Import', "preview");
	}

}
