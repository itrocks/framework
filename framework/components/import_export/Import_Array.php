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

	//--------------------------------------------------------------------------- addConstantsToArray
	/**
	 * Adds properties path and values of constants to the whole array
	 *
	 * The cursor on $array must be set to properties path row before calling this method
	 * The cursor will stay on the properties path row
	 *
	 * @param $this_constants string[] key is property path, value will be written into array
	 * @param $array          array Two dimensional array : keys are row and column nummber
	 */
	private static function addConstantsToArray($this_constants, &$array)
	{
		if ($this_constants) {
			$constants = array();
			$column_first = $column_number = count(current($array));
			// $this_constants["property*.path"] = "value"
			// $constants[$column_number] = "value"
			foreach ($this_constants as $value) {
				$constants[$column_number++] = $value;
			}
			$properties_key = key($array);
			$column_number = $column_first;
			foreach (array_keys($this_constants) as $property_path) {
				$array[$properties_key][$column_number++] = $property_path;
			}
			while ($row = next($array)) {
				$key = key($array);
				foreach ($constants as $column_number => $value) {
					$array[$key][$column_number] = $value;
				}
			}
			// sets array cursor to original properties path position
			reset($array);
			while (key($array) !== $properties_key) {
				next($array);
			}
		}
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
	 * The cursor on $array is reset to the first row of the array
	 *
	 * @param $array array Two dimensional array : keys are row and column number
	 * @return string
	 */
	public static function getClassNameFromArray(&$array)
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

	//------------------------------------------------------------------------- getConstantsFromArray
	/**
	 * Gets the constants properties path and value from array
	 *
	 * The cursor on $array is set to the row containing the definitive properties path
	 *
	 * @param $array array Two dimensional array : keys are row and column number
	 * @return string[] key is property path, value is it's fixed value for the import
	 */
	public static function getConstantsFromArray(&$array)
	{
		$constants = array();
		$row = (self::getClassNameFromArray($array)) ? next($array) : current($array);
		while ($row && (count($row) > 1) && ($row[1] == "=")) {
			$constants[$row[0]] = isset($row[2]) ? $row[2] : "";
			$row = next($array);
		}
		return $constants;
	}

	//------------------------------------------------------------------------ getPropertiesFromArray
	/**
	 * Returns the properties paths list of the array
	 *
	 * The property list is the first or the second line of the array, depending on if the first line
	 * is a class name or not.
	 *
	 * The cursor on $array is set to the row containing the properties path.
	 *
	 * @param $array array Two dimensional array : keys are row and column number
	 * @return string[] key is the column number, value is a property path
	 */
	public static function getPropertiesFromArray(&$array)
	{
		self::addConstantsToArray(self::getConstantsFromArray($array), $array);
		$properties = array();
		foreach (current($array) as $key => $value) {
			if ($value) {
				$properties[$key] = $value;
			}
		}
		return $properties;
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
			$this->setClassName($class_name);
		}
		list($this->properties_link, $this->properties_column) = self::getPropertiesLinkAndColumn(
			$this->class_name, self::getPropertiesFromArray($array)
		);
		$key = key($array);
		foreach ($this->sortedClasses() as $class) {
			reset($array);
			while (key($array) !== $key) {
				next($array);
			}
			$this->importArrayClass($class, $array);
		}
	}

	//------------------------------------------------------------------------------ importArrayClass
	/**
	 * Imports data stored into array
	 *
	 * The cursor on $array must be set to the properties path row before calling this method
	 * At the end, the cursor will be at the end of the array (out of the array, in fact)
	 *
	 * @param $class Import_Class
	 * @param $array array Two dimensional array : keys are row and column number
	 */
	private function importArrayClass(Import_Class $class, &$array)
	{
		$property_path = implode(".", $class->property_path);
		/** @var $class_properties_column integer[] key is the property name of the current class */
		$class_properties_column = $this->properties_column[$property_path];
		while ($row = next($array)) {
			$irow = key($array);
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
