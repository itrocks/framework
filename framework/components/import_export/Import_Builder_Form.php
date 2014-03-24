<?php
namespace SAF\Framework;

/**
 * Import builder from import form data
 */
abstract class Import_Builder_Form
{

	//----------------------------------------------------------------------------------------- build
	/**
	 * @param $form  array
	 * @param $files File[] must be set with same keys as $form['worksheets'] array has
	 * @return Import
	 */
	public static function build($form, $files)
	{
		$import = new Import();
		if (isset($form['worksheets'])) {
			foreach ($form['worksheets'] as $worksheet_name => $worksheet) {
				if (isset($files[$worksheet_name])) {
					$settings = Import_Settings_Builder::buildForm($worksheet);
					$import->worksheets[$worksheet_name] = new Import_Worksheet(
						$worksheet_name, $settings, $files[$worksheet_name]
					);
				}
			}
		}
		return $import;
	}

}
