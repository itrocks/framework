<?php
namespace SAF\Framework;

/**
 * Import builder from import form data
 */
abstract class Import_Builder_Form
{

	//----------------------------------------------------------------------------------------- build
	/**
	 * @param $form array
	 * @return Import
	 */
	public static function build($form, $with_preview = false)
	{
		//echo "<h2>IMPORT SETTINGS FORM CONTENT :</h2><pre>" . print_r($form, true) . "</pre>";
		$import = new Import();
		if (isset($form["worksheets"])) {
			foreach ($form["worksheets"] as $worksheet_name => $worksheet) {
				$settings = Import_Settings_Builder::buildForm($worksheet);
				$file_name = Application::current()->getTemporaryFilesPath()
					. "/" . $worksheet["file"]["name"];
				$file = new File($file_name);
				$import->worksheets[$worksheet_name] = new Import_Worksheet(
					$worksheet_name,
					$settings,
					$file
				);
			}
		}
		//echo "<h2>IMPORT SETTINGS ARE READY :</h2><pre>" . print_r($import, true) . "</pre>";
		return $import;
	}

}
