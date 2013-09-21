<?php
namespace SAF\Framework;

/**
 * Import data into the application from array
 */
class Import_Array
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//---------------------------------------------------------------------------- $properties_column
	/**
	 * $properties_column[$property_path][$property_name]
	 *
	 * @var array Two dimensional array : keys are property path without final name and property name
	 */
	private $properties_column;

	//------------------------------------------------------------------------------ $properties_link
	/**
	 * $properties_link[$property_path] = value of the property @link annotation
	 *
	 * @var string[] key is the property path
	 */
	private $properties_link;

	//------------------------------------------------------------------------------------- $settings
	/**
	 * @var Import_Settings
	 */
	public $settings;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $settings   Import_Settings
	 * @param $class_name string
	 */
	public function __construct(Import_Settings $settings = null, $class_name = null)
	{
		if (isset($settings))   $this->settings   = $settings;
		if (isset($class_name)) $this->class_name = $class_name;
	}

	//-------------------------------------------------------------------------- createArrayReference
	/**
	 * @param $class_name string
	 * @param $search     array
	 * @return array
	 */
	private function createArrayReference($class_name, $search)
	{
		if (isset($search)) {
			$object = Builder::create($class_name);
			foreach ($search as $property_name => $value) {
				$object->$property_name = $value;
			}
			$object = array($object);
		}
		else {
			$object = null;
		}
		return $object;
	}

	//------------------------------------------------------------------------- getClassNameFromArray
	/**
	 * Returns class name if the first value of the array is a class name
	 *
	 * A class name is alone of its row, and begins with an uppercase letter
	 * If the first row of the array is not a class name, this will return null
	 *
	 * @param $array array Two dimensional array : keys are row and column number
	 * @return string
	 */
	public static function getClassNameFromArray($array)
	{
		$row = reset($array);
		$class_name = reset($row);
		return (
			$class_name
			&& ($class_name[0] === strtoupper($class_name[0]))
			&& (count($row) == 1) || !$row[1]
		) ? self::getClassNameFromValue($class_name)
			: null;
	}

	//------------------------------------------------------------------------- getClassNameFromValue
	/**
	 * @param $value  string class name taken from the import array
	 * @return string class name (built and with added namespace, if needed)
	 */
	public static function getClassNameFromValue($value)
	{
		return Builder::className(Namespaces::fullClassName($value));
	}

	//------------------------------------------------------------------------ getPropertiesFromArray
	/**
	 * Returns the properties paths list of the array
	 *
	 * The property list is the first or the second line of the array, depending on if the first line
	 * is a class name or not.
	 *
	 * @param $array array Two dimensional array : keys are row and column number
	 * @return string[] key is the column number, value is a property path
	 */
	public static function getPropertiesFromArray($array)
	{
		reset($array);
		return self::getClassNameFromArray($array) ? next($array) : current($array);
	}

	//---------------------------------------------------------------------- getPropertyLinkAndColumn
	/**
	 * @param $class_name      string Main class name
	 * @param $properties_path string[]
	 * @return array First element is property link, second is property column
	 */
	private static function getPropertiesLinkAndColumn($class_name, $properties_path)
	{
		$properties_link = array("" => "Object");
		$properties_column = array();
		foreach ($properties_path as $icol => $property_path) {
			$property_path = str_replace("*", "", $property_path);
			$path = "";
			foreach (explode(".", $property_path) as $property_name) {
				$path .= ($path ? "." : "") . $property_name;
				$property = Reflection_Property::getInstanceOf($class_name, $path);
				$properties_link[$path] = $property->getAnnotation("link")->value;
			}
			$i = strrpos($property_path, ".");
			$property_name = substr($property_path, ($i === false) ? 0 : ($i + 1));
			$property_path = substr($property_path, 0, $i);
			$properties_column[$property_path][$property_name] = $icol;
		}
		foreach (array_keys($properties_column) as $property_path) if ($property_path) {
			$path = "";
			foreach (explode(".", $property_path) as $property_name) {
				if (!isset($properties_column[$path][$property_name])) {
					$properties_column[$path][$property_name] = $path . ($path ? "." : "") . $property_name;
				}
				$path .= ($path ? "." : "") . $property_name;
			}
		}
		return array($properties_link, $properties_column);
	}

	//------------------------------------------------------------------------------- getSearchObject
	/**
	 * @param $row                     string[] imported row of data
	 * @param $identify_properties     Import_Property[] properties used to identify the read object
	 * @param $class_properties_column integer[] column index for each property of the current class
	 * @return array Dao::search compliant array / objects
	 */
	private function getSearchObject($row, $identify_properties, $class_properties_column)
	{
		$empty_object = true;
		$search = array();
		foreach (array_keys($identify_properties) as $property_name) {
			$value = $row[$class_properties_column[$property_name]];
			$search[$property_name] = $value;
			$empty_object = $empty_object && empty($value);
		}
		return $empty_object ? null : $search;
	}

	//----------------------------------------------------------------------------------- importArray
	/**
	 * Imports a data array using settings
	 *
	 * $array is a reference to avoid array replication.
	 * Beware : if $array begins with a "Class_Name" row, this first row will be removed !
	 * Beware : first row must contain property paths, and will be removed !
	 *
	 * @param $array array two dimension (keys are row and column number) array
	 */
	public function importArray(&$array)
	{
		$class_name = self::getClassNameFromArray($array);
		if (isset($class_name)) {
			unset($array[key($array)]);
			$this->setClassName($class_name);
		}
		list($this->properties_link, $this->properties_column) = self::getPropertiesLinkAndColumn(
			$this->class_name, self::getPropertiesFromArray($array)
		);
		unset($array[key($array)]);

		foreach ($this->sortedClasses() as $class) {
			$this->importArrayClass($class, $array);
		}
	}

	//------------------------------------------------------------------------------ importArrayClass
	/**
	 * @param $class Import_Class
	 * @param $array array Two dimensional array : keys are row and column number
	 */
	private function importArrayClass(Import_Class $class, &$array)
	{
		$property_path = implode(".", $class->property_path);
		/** @var $class_properties_column integer[] key is the property name of the current class */
		$class_properties_column = $this->properties_column[$property_path];
		foreach ($array as $irow => $row) {
			$search = $this->getSearchObject($row, $class->identify_properties, $class_properties_column);
			if (in_array($this->properties_link[$property_path], array("Collection", "Map"))) {
				$object = $this->createArrayReference($class->class_name, $search);
			}
			else {
				$found = isset($search) ? Dao::search($search, $class->class_name) : null;
				if (!isset($found)) {
					$object = null;
				}
				elseif (count($found) == 1) {
					$object = $this->updateExistingObject(
						reset($found), $row, $class, $class_properties_column
					);
				}
				elseif (!count($found)) {
					if ($class->object_not_found_behaviour === "create_new_value") {
						$object = $this->writeNewObject($row, $class, $class_properties_column);
					}
					elseif ($class->object_not_found_behaviour === "tell_it_and_stop_import") {
						trigger_error(
							"Not found " . $class->class_name . " " . print_r($search, true), E_USER_ERROR
						);
						$object = null;
					}
					else {
						$object = null;
					}
				}
				else {
					trigger_error(
						"Multiple $class->class_name found for " . print_r($search, true), E_USER_ERROR
					);
					$object = -1;
				}
			}
			$array[$irow][$property_path] = $object;
		}
	}

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * Sets class name
	 *
	 * If class name was already set, checks if it is the same. If not, this will trigger an error.
	 * To change class name, do not use setClassName and directly access to the $class_name property.
	 *
	 * @param $class_name string
	 */
	private function setClassName($class_name)
	{
		if (isset($this->class_name)) {
			if ($class_name !== $this->class_name) {
				trigger_error("Bad class name " . $class_name . ", should be a " . $this->class_name);
			}
		}
		$this->class_name = $class_name;
	}

	//--------------------------------------------------------------------------------- sortedClasses
	/**
	 * Sorts classes and returns a class list, starting from the one which has the more depth
	 *
	 * @return Import_Class[]
	 */
	private function sortedClasses()
	{
		uksort($this->settings->classes, function($class_path_1, $class_path_2)
		{
			return substr_count($class_path_1, ".") < substr_count($class_path_2, ".");
		});
		return $this->settings->classes;
	}

	//-------------------------------------------------------------------------- updateExistingObject
	/**
	 * @param $object                  object
	 * @param $row                     array
	 * @param $class                   Import_Class
	 * @param $class_properties_column integer[]|string[]
	 * @return object
	 */
	private function updateExistingObject(
		$object, $row, Import_Class $class, $class_properties_column
	) {
		$do_write = false;
		foreach (array_keys($class->write_properties) as $property_name) {
			if ($object->$property_name !== $row[$class_properties_column[$property_name]]) {
				$object->$property_name = $row[$class_properties_column[$property_name]];
				$do_write = true;
			}
		}
		if ($do_write) {
			Dao::write($object);
		}
		return $object;
	}

	//-------------------------------------------------------------------------------- writeNewObject
	/**
	 * @param $row                     array
	 * @param $class                   Import_Class
	 * @param $class_properties_column integer[]|string[]
	 * @return object
	 */
	private function writeNewObject($row, Import_Class $class, $class_properties_column)
	{
		$object = Builder::create($class->class_name);
		foreach (array_keys($class->identify_properties) as $property_name) {
			$object->$property_name = $row[$class_properties_column[$property_name]];
		}
		foreach (array_keys($class->write_properties) as $property_name) {
			$object->$property_name = $row[$class_properties_column[$property_name]];
		}
		Dao::write($object);
		return $object;
	}

}
