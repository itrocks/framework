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
	public static function build($form)
	{
		//echo "<h2>IMPORT SETTINGS FORM CONTENT :</h2><pre>" . print_r($form, true) . "</pre>";
		$import = new Import();
		if (isset($form["worksheets"])) {
			foreach ($form["worksheets"] as $worksheet_name => $worksheet) {
				$settings = self::buildSettings($worksheet);
				$file_name = Application::current()->getTemporaryFilesPath()
					. "/" . $worksheet["file"]["name"];
				$file = new File($file_name);
				$preview = new Import_Preview(array_map("str_getcsv", file($file_name)));
				$import->worksheets[$worksheet_name] = new Import_Worksheet(
					$worksheet_name,
					$settings,
					$preview,
					$file
				);
			}
		}
		//echo "<h2>IMPORT SETTINGS ARE READY :</h2><pre>" . print_r($import, true) . "</pre>";
		return $import;
	}

	//--------------------------------------------------------------------------------- buildSettings
	/**
	 * @param $worksheet array
	 * @return Import_Settings
	 */
	private static function buildSettings($worksheet)
	{
		$settings = new Import_Settings();
		$main_class_name = null;
		foreach ($worksheet["classes"] as $property_path => $class) {
			if ($property_path[0] === strtoupper($property_path[0])) {
				// the first element is always the main class name
				$class_name = $main_class_name = Namespaces::fullClassName($property_path);
				$property_path = "";
			}
			else {
				// property paths for next elements
				$property_path = str_replace(">", ".", $property_path);
				$property = Reflection_Property::getInstanceOf($main_class_name, $property_path);
				$class_name = $property->getType()->getElementTypeAsString();
			}
			$class_key = Namespaces::shortClassName($main_class_name)
				. ($property_path ? ("." . $property_path) : "");
			$settings->classes[$class_key] = self::buildClass($class_name, $property_path, $class);
		}
		return $settings;
	}

	//------------------------------------------------------------------------------------ buildClass
	/**
	 * @param $class_name    string
	 * @param $property_path string
	 * @param $class         string[]
	 * @return Import_Class
	 */
	private static function buildClass($class_name, $property_path, $class)
	{
		$property_path = $property_path ? explode(".", $property_path) : array();
		$import_class = new Import_Class(
			$class_name, $property_path, $class["object_not_found_behaviour"]
		);
		if ($class["identify"]) {
			foreach (explode(",", $class["identify"]) as $property_name) {
				$import_class->identify_properties[$property_name] = new Import_Property(
					$class_name, $property_name
				);
			}
		}
		if ($class["write"]) {
			foreach (explode(",", $class["write"]) as $property_name) {
				$import_class->write_properties[$property_name] = new Import_Property(
					$class_name, $property_name
				);
			}
		}
		if ($class["ignore"]) {
			foreach (explode(",", $class["ignore"]) as $property_name) {
				$import_class->ignore_properties[$property_name] = new Import_Property(
					$class_name, $property_name
				);
			}
		}
		return $import_class;
	}

}
