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

	//----------------------------------------------------------------------------------- $properties
	/**
	 * Accessed properties cache, per class name
	 *
	 * @var array key is a class name, each element is a Reflection_Property[]
	 */
	private $properties = array();

	//------------------------------------------------------------------------------------- $settings
	/**
	 * @var Import_Settings
	 */
	public $settings;

	//----------------------------------------------------------------------------------- $will_group
	/**
	 * multi-dimensional (keys are class name, id, property name)
	 * value is array($object, array value of property)
	 *
	 * @var array
	 */
	private $will_group = array();

	//----------------------------------------------------------------------------------- $will_write
	/**
	 * Objects that will be written
	 *
	 * @var object[]
	 */
	private $will_write = array();

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

	//----------------------------------------------------------------------------------- groupObject
	/**
	 * @param $object     object
	 * @param $properties Reflection_Property[]
	 * @param $has        object[]
	 * @return object
	 */
	private function groupObject($object, $properties, &$has)
	{
		// recurse
		foreach ($properties as $property) if (!$property->isStatic()) {
			$property_name = $property->name;
			if (isset($object->$property_name) && ($value = $object->$property_name)) {
				if (is_object($value)) {
					$sub_class_name = get_class($value);
					if (!isset($this->properties[$sub_class_name])) {
						$class = Reflection_Class::getInstanceOf($sub_class_name);
						$this->properties[$sub_class_name] = $class->accessProperties();
					}
					$no_has = array();
					$this->groupObject($value, $this->properties[$sub_class_name], $no_has);
				}
				elseif (is_array($value) && is_object(reset($value))) {
					$object->$property_name = $this->groupObjects(get_class(current($value)), $value);
				}
			}
		}
		// check if must group
		if ($id = Dao::getObjectIdentifier($object)) {
			if (isset($has[$id])) {
				$already = $has[$id];
				foreach ($properties as $property) if (!$property->isStatic()) {
					$property_name = $property->name;
					if (isset($object->$property_name) && is_array($object->$property_name)) {
						$sub_objects = isset($already->$property_name)
							? array_merge($already->$property_name, $object->$property_name)
							: $object->$property_name;
						if ($sub_objects) {
							$will_group[get_class($object)][$property_name][$id] = array($object, $sub_objects);
						}
						$already->$property_name = $sub_objects;
					}
				}
				$object = null;
			}
			else {
				$has[$id] = $object;
			}
		}
		return $object;
	}

	//---------------------------------------------------------------------------------- groupObjects
	/**
	 * When several identical objects : cumulate their mapped / collection elements
	 *
	 * Objects must be of the same class
	 *
	 * @param $objects object[]
	 * @param $class_name
	 * @return object[]
	 */
	private function groupObjects($class_name, $objects)
	{
		if (!isset($this->properties[$class_name])) {
			$class = Reflection_Class::getInstanceOf($class_name);
			$this->properties[$class_name] = $class->accessProperties();
		}
		$properties = $this->properties[$class_name];
		$has = array();
		foreach ($objects as $key => $object) {
			if (is_null($this->groupObject($object, $properties, $has))) {
				unset($objects[$key]);
			}
		}
		return $objects;
	}

	//------------------------------------------------------------------------------- groupObjectsEnd
	private function groupObjectsEnd()
	{
		while ($will_group = $this->will_group) {
			$this->will_group = array();
			foreach ($will_group as $group2) {
				foreach ($group2 as $group3) {
					foreach ($group3 as $property_name => $group) {
						list($object, $value) = $group;
						$object->$property_name = $this->groupObjects(get_class(reset($value)), $value);
					}
				}
			}
		}
		// release accessed properties
		foreach (array_keys($this->properties) as $class_name) {
			Reflection_Class::getInstanceOf($class_name)->accessPropertiesDone();
		}
		$this->properties = array();
	}

	//----------------------------------------------------------------------------------- importArray
	/**
	 * Imports a data array using settings
	 *
	 * $array is a reference to avoid array replication.
	 * Beware : if $array begins with a "Class_Name" row, this first row will be removed !
	 *
	 * @param $array array two dimension (keys are row and column number) array
	 */
	public function importArray(&$array)
	{
		// first row may contain class name if only one value beggining with an uppercase character
		$row = reset($array);
		$class_name = reset($row);
		if (($class_name[0] === strtoupper($class_name[0])) && (count($row) == 1) || !$row[1]) {
			if (isset($this->class_name)) {
				$want_class_name = Namespaces::shortClassName($this->class_name);
				if ($class_name !== $want_class_name) {
					trigger_error("Bad class name " . $class_name . ", should be a " . $want_class_name);
				}
			}
			else {
				$this->class_name = Namespaces::fullClassName($class_name);
			}
			unset($array[key($array)]);
		}
		// sets $property_column[$property_path][$property_name] = $icol
		$property_column = array();
		foreach (reset($array) as $icol => $property_path) {
			$property_path = str_replace("*", "", $property_path);
			$i = strrpos($property_path, ".");
			$property_name = substr($property_path, ($i === false) ? 0 : ($i + 1));
			$property_path = substr($property_path, 0, $i);
			$property_column[$property_path][$property_name] = $icol;
		}
		foreach (array_keys($property_column) as $property_path) {
			$i = 0;
			while ($i = strpos($property_path, ".", $i)) {
				$property_name = substr($property_path, 0, $i);
				if (!isset($property_column[$property_name])) {
					$property_column[$property_name] = $property_name;
				}
				$i++;
			}
		}
		echo "<pre>columns = " . print_r($property_column, true) . "</pre>";
		foreach ($this->sortedClasses() as $class) {
			echo "<pre>class $class->class_name " . print_r($class, true) . "</pre>";
			$property_path = implode(".", $class->property_path);
			$property_cols = $property_column[$property_path];
			reset($array);
			echo "property_cols for $property_path = " . print_r($property_cols, true) . "<br>";
			while (is_array($row = next($array))) {
				$empty_object = true;
				$search = array();
				foreach (array_keys($class->identify_properties) as $property_name) {
					$value = $row[$property_cols[$property_name]];
					$search[$property_name] = $value;
					$empty_object = $empty_object && empty($value);
				}
echo "search " . print_r($search, true) . "<br>";
				$found = $empty_object ? null : Dao::search($search, $class->class_name);
echo "found " . print_r($found, true) . "<br>";
				if ($found && count($found) == 1) {
					$object = reset($found);
					$do_write = false;
					foreach (array_keys($class->write_properties) as $property_name) {
						if ($object->$property_name !== $row[$property_cols[$property_name]]) {
							$object->$property_name = $row[$property_cols[$property_name]];
							$do_write = true;
						}
					}
					if ($do_write) {
echo "WILL UPDATE " . print_r($object, true) . "<br>";
						$this->will_write[] = $object;
					}
				}
				elseif (isset($found) && !count($found)) {
					if ($class->object_not_found_behaviour === "create_new_value") {
						$object = Builder::create($class->class_name);
						foreach (array_keys($class->identify_properties) as $property_name) {
							$object->$property_name = $row[$property_cols[$property_name]];
						}
						foreach (array_keys($class->write_properties) as $property_name) {
							$object->$property_name = $row[$property_cols[$property_name]];
						}
echo "WILL CREATE " . print_r($object, true) . "<br>";
						$this->will_write[] = $object;
					}
					elseif ($class->object_not_found_behaviour === "tell_it_and_stop_import") {
						trigger_error(
							"Not found " . $class->class_name . " " . print_r($search, true), E_USER_ERROR
						);
					}
				}
			}
		}
		/*
		// TODO Remove these comments : that was not the good method
		echo "<pre>SETTINGS = " . print_r($this->settings, true) . "</pre>";
		// first row now contains the list of properties path : must be updated
		$array[key($array)] = $this->updatePropertyPath(reset($array));
		// build objects
		$builder = new Object_Builder_Array($this->class_name);
		$objects = $builder->buildCollection($this->class_name, $array, true);
		$objects = $this->groupObjects($this->class_name, $objects);
		$this->groupObjectsEnd();
		//echo "<pre>IMPORT " . print_r($objects, true) . "</pre>";
		// import objects
		$class_path = Namespaces::shortClassName($this->class_name);
		foreach ($objects as $object) {
			$this->importObject($object, $class_path);
		}
		*/
	}

	//---------------------------------------------------------------------------------- importObject
	/**
	 * @param $object     object
	 * @param $class_path string
	 */
	public function importObject($object, $class_path)
	{
		$class = $this->settings->classes[$class_path];
		echo "- import " . $object . " to $class_path<br>";
		echo "<pre>OBJECT = " . print_r($object, true) . "</pre>";
		foreach ($this->settings->classes as $class_path => $class) {

		}
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

	//---------------------------------------------------------------------------- updatePropertyPath
	/**
	 * Gets identify properties from settings and put * into input array properties paths
	 *
	 * @param $array string[]
	 * @return string[]
	 */
	private function updatePropertyPath($array)
	{
		$main_class_path = Namespaces::shortClassName($this->class_name);
		foreach ($array as $column => $property_path) {
			$class_path = $main_class_path;
			$property_path = explode(".", $property_path);
			foreach ($property_path as $path_key => $property_name) {
				if (substr($property_name, -1) === "*") {
					$property_name = substr($property_name, 0, -1);
				}
				$identify = isset(
					$this->settings->classes[$class_path]->identify_properties[$property_name]
				);
				$property_path[$path_key] = $property_name . ($identify ? "*" : "");
				$class_path .= "." . $property_name;
			}
			$array[$column] = join(".", $property_path);
		}
echo "property path = " . print_r($array, true) . "<br>";
		return $array;
	}

}
