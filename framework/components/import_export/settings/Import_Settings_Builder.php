<?php
namespace SAF\Framework;

/**
 * Import settings builder
 */
abstract class Import_Settings_Builder
{

	//------------------------------------------------------------------------------------ buildArray
	/**
	 * Builds import settings using a data array
	 *
	 * First line must contain the class name (can be a short name, namespace will automatically be found)
	 * Second line must contain the fields paths, relative to the class
	 * Other liens contain data, and are not used
	 *
	 * @param $array            array two dimensional array (keys are row, col)
	 * @return Import_Settings
	 */
	public static function buildArray(&$array)
	{
		$class_name = Import_Array::getClassNameFromArray($array);
		$settings = new Import_Settings($class_name);
		/** @var $classes Import_Class[] */
		$classes = array();
		foreach (Import_Array::getPropertiesFromArray($array, $class_name) as $property_path) {
			$sub_class = $class_name;
			$last_identify = false;
			$class_path = "";
			$property_path_for_class = array();
			foreach (explode(".", $property_path) as $property_name) {
				$identify = (substr($property_name, -1) !== "*");
				if (!$identify) {
					$property_name = substr($property_name, 0, -1);
				}
				$class_key = join(".", $property_path_for_class);
				if (!isset($classes[$class_key])) {
					$classes[$class_key] = new Import_Class(
						$sub_class,
						$property_path_for_class,
						$last_identify ? "tell_it_and_stop_import" : "create_new_value"
					);
				}
				$class = $classes[$class_key];
				$property = new Import_Property($sub_class, $property_name);
				if ($identify) {
					$class->identify_properties[$property_name] = $property;
				}
				else {
					$class->write_properties[$property_name] = $property;
				}
				$property = Reflection_Property::getInstanceOf($sub_class, $property_name);
				$sub_class = Builder::className($property->getType()->getElementTypeAsString());
				$last_identify = $identify;
				$class_path .= $sub_class . ".";
				$property_path_for_class[] = $property_name;
			}
		}
		$settings->classes = $classes;
		return $settings;
	}

	//------------------------------------------------------------------------------------- buildForm
	/**
	 * Builds import settings using a recursive array coming from an input form
	 *
	 * @param $worksheet  array
	 * @return Import_Settings
	 */
	public static function buildForm($worksheet)
	{
		$main_class_name = null;
		$settings = new Import_Settings();
		foreach ($worksheet["classes"] as $property_path => $class) {
			if ($property_path[0] === strtoupper($property_path[0])) {
				// the first element is always the main class name
				$class_name = $main_class_name = Namespaces::fullClassName($property_path);
				$settings->class_name = $class_name;
				$property_path = "";
			}
			else {
				// property paths for next elements
				$property_path = str_replace(">", ".", $property_path);
				$property = Reflection_Property::getInstanceOf($main_class_name, $property_path);
				$class_name = Builder::className($property->getType()->getElementTypeAsString());
			}
			$settings->classes[$property_path] = self::buildFormClass(
				$class_name, $property_path, $class
			);
		}
		return $settings;
	}

	//-------------------------------------------------------------------------------- buildFormClass
	/**
	 * @param $class_name    string
	 * @param $property_path string
	 * @param $class         string[]
	 * @return Import_Class
	 */
	private static function buildFormClass($class_name, $property_path, $class)
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
