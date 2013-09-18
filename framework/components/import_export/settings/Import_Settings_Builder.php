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
	 * @param $array array two dimensional array (keys are row, col)
	 * @return Import_Settings
	 */
	public static function buildArray($array)
	{
		$settings = new Import_Settings();
		$row = reset($array);
		$class_name = Namespaces::fullClassName(reset($row));
		/** @var $classes Import_Class[] */
		$classes = array();
		foreach (next($array) as $property_path) {
			$sub_class = $class_name;
			$last_identify = false;
			$class_path = "";
			$class_path_display = array();
			foreach (explode(".", $property_path) as $property_name) {
				$class_path_display[] = Names::classToDisplay($sub_class);
				$identify = substr($property_name, -1) === "*";
				if ($identify) {
					$property_name = substr($property_name, 0, -1);
				}
				if (!isset($classes[$class_path . $sub_class])) {
					$classes[$class_path . $sub_class] = new Import_Class(
						$sub_class,
						$last_identify ? "tell_it_and_stop_import" : "create_new_value",
						$class_path_display
					);
				}
				$class = $classes[$class_path . $sub_class];
				$property = new Import_Property($sub_class, $property_name);
				if ($identify) {
					$class->identify_properties[$property_name] = $property;
				}
				else {
					$class->write_properties[$property_name] = $property;
				}
				$property = Reflection_Property::getInstanceOf($sub_class, $property_name);
				$sub_class = $property->getType()->getElementTypeAsString();
				$last_identify = $identify;
				$class_path .= $sub_class . ".";
			}
		}
		$settings->classes = $classes;
		return $settings;
	}

}
