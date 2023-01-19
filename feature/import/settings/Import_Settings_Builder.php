<?php
namespace ITRocks\Framework\Feature\Import\Settings;

use ITRocks\Framework\Feature\Import\Import_Array;
use ITRocks\Framework\Reflection\Annotation\Class_\Identify_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Representative_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ReflectionException;

/**
 * Import settings builder
 */
abstract class Import_Settings_Builder
{

	//---------------------------------------------------------------------------------- autoIdentify
	/**
	 * If no property contains the character '*' in import file, automatically detects which property
	 * names are used to identify records using the identify/representative classes annotation
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name      string
	 * @param $properties_path string[] $property_path = string[integer $column_number]
	 * @return array $identified = boolean[string $property_path][integer $position]
	 */
	private static function autoIdentify(string $class_name, array $properties_path) : array
	{
		foreach ($properties_path as $property_path) {
			if (str_contains($property_path, '*')) {
				return [];
			}
		}
		$auto_identify = [];
		/** @noinspection PhpUnhandledExceptionInspection $class_name must be valid */
		$root_class = new Reflection_Class($class_name);
		foreach ($properties_path as $property_path) {
			$class = $root_class;
			foreach (explode(DOT, $property_path) as $pos => $property_name) {
				if (
					in_array($property_name, Identify_Annotation::of($class)->values())
					|| in_array($property_name, Representative_Annotation::of($class)->values())
				) {
					$auto_identify[$property_path][$pos] = true;
				}
				try {
					/** @noinspection PhpUnhandledExceptionInspection Property path must be valid */
					$property = $class->getProperty($property_name);
					$type     = $property->getType();
					if ($type->isClass()) {
						$class = $type->asReflectionClass();
					}
				}
				catch (ReflectionException) {
					// nothing
				}
			}
		}
		return $auto_identify;
	}

	//------------------------------------------------------------------------------------ buildArray
	/**
	 * Builds import settings using a data array
	 *
	 * First line must contain the class name (can be a short name, namespace will automatically be found)
	 * Second line must contain the fields paths, relative to the class
	 * Other liens contain data, and are not used
	 *
	 * @param $array      array two-dimensional array (keys are row, col)
	 * @param $class_name string|null default class name (if not found into array)
	 * @return Import_Settings
	 */
	public static function buildArray(array &$array, string $class_name = null) : Import_Settings
	{
		$class_name = Import_Array::getClassNameFromArray($array) ?: $class_name;
		$settings   = new Import_Settings($class_name);
		/** @var $classes Import_Class[] */
		$classes         = [];
		$properties_path = Import_Array::getPropertiesFromArray($array, $class_name);
		$auto_identify   = self::autoIdentify($class_name, $properties_path);
		foreach ($properties_path as $property_path) {
			$sub_class               = $class_name;
			$last_identify           = false;
			$property_path_for_class = [];
			foreach (explode(DOT, $property_path) as $pos => $property_name) {
				$identify = !str_ends_with($property_name, '*');
				if (!$identify) {
					$property_name = substr($property_name, 0, -1);
				}
				$class_key = join(DOT, $property_path_for_class);
				if (!isset($classes[$class_key])) {
					$classes[$class_key] = new Import_Class(
						$sub_class,
						$property_path_for_class,
						$last_identify ? Behaviour::TELL_IT_AND_STOP_IMPORT : Behaviour::CREATE_NEW_VALUE
					);
				}
				$class           = $classes[$class_key];
				$import_property = new Import_Property($sub_class, $property_name);
				try {
					$property = new Reflection_Property($sub_class, $property_name);
					if (
						($identify && !$auto_identify)
						|| (
							isset($auto_identify[$property_path]) && isset($auto_identify[$property_path][$pos])
						)
					) {
						$class->identify_properties[$property_name] = $import_property;
					}
					else {
						$class->properties[$property_name]       = $property;
						$class->write_properties[$property_name] = $import_property;
					}
					$sub_class = $property->getType()->getElementTypeAsString();
				}
				catch (ReflectionException) {
					$class->ignore_properties[$property_name]  = $import_property;
					$class->unknown_properties[$property_name] = $import_property;
				}
				$last_identify             = $identify;
				$property_path_for_class[] = $property_name;
			}
		}
		$settings->classes = $classes;
		$settings->setConstants(Import_Array::getConstantsFromArray($array));
		return $settings;
	}

	//------------------------------------------------------------------------------------- buildForm
	/**
	 * Builds import settings using a recursive array coming from an input form
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $worksheet array
	 * @return Import_Settings
	 * @see Functions::escapeName()
	 */
	public static function buildForm(array $worksheet) : Import_Settings
	{
		$main_class_name = null;
		$settings        = new Import_Settings();
		if (isset($worksheet['name'])) {
			$settings->name = $worksheet['name'];
		}
		if (isset($worksheet['classes'])) {
			foreach ($worksheet['classes'] as $property_path => $class) {
				if (ctype_upper($property_path[0])) {
					// the first element is always the main class name
					$class_name = $main_class_name = $property_path;
					$settings->setClassName($class_name);
					$property_path = '';
				}
				else {
					// property paths for next elements
					$property_path = str_replace(['>', Q, BQ], [DOT, '(', ')'], $property_path);
					/** @noinspection PhpUnhandledExceptionInspection class and property are valid */
					$property   = new Reflection_Property($main_class_name, $property_path);
					$class_name = $property->getType()->getElementTypeAsString();
				}
				$settings->classes[$property_path] = self::buildFormClass(
					$class_name, $property_path, $class
				);
			}
		}
		return $settings;
	}

	//-------------------------------------------------------------------------------- buildFormClass
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name    string
	 * @param $property_path string
	 * @param $class         array
	 * @return Import_Class
	 */
	private static function buildFormClass(string $class_name, string $property_path, array $class)
		: Import_Class
	{
		$property_path = $property_path ? explode(DOT, $property_path) : [];
		$import_class  = new Import_Class(
			$class_name, $property_path, $class['object_not_found_behaviour']
		);
		if (isset($class['constants']) && is_array($class['constants'])) {
			foreach ($class['constants'] as $constant) {
				/** @noinspection PhpUnhandledExceptionInspection property for constants are valid */
				$import_class->constants[$constant['name']] = new Reflection_Property_Value(
					$import_class->class_name, $constant['name'], $constant['value'], true
				);
			}
		}
		if ($class['identify']) {
			foreach (explode(',', $class['identify']) as $property_name) {
				$import_class->identify_properties[$property_name] = new Import_Property(
					$class_name, $property_name
				);
			}
		}
		if ($class['write']) {
			foreach (explode(',', $class['write']) as $property_name) {
				$import_property = new Import_Property($class_name, $property_name);
				$import_class->write_properties[$property_name] = $import_property;
				try {
					$import_class->properties[$property_name]
						= new Reflection_Property($class_name, $property_name);
				}
				catch (ReflectionException) {
					$import_class->unknown_properties[$property_name] = $import_property;
				}
			}
		}
		if ($class['ignore']) {
			foreach (explode(',', $class['ignore']) as $property_name) {
				$import_class->ignore_properties[$property_name] = new Import_Property(
					$class_name, $property_name
				);
			}
		}
		return $import_class;
	}

}
