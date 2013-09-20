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
		Mysql_Logger::getInstance()->continue = true;
		Mysql_Logger::getInstance()->display_log = true;
		// --- first row may contain class name if only one value beggining with an uppercase character
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

		// --- sets $property_column[$property_path][$property_name] = $icol
		/** @var $property_class_name string[] */
		$property_class_name = array();
		/** @var $property_link string[] */
		$property_link = array("" => "Object");
		$property_column = array();
		foreach (reset($array) as $icol => $property_path) {
			$property_path = str_replace("*", "", $property_path);
			$path = "";
			foreach (explode(".", $property_path) as $property_name) {
				$path .= ($path ? "." : "") . $property_name;
				$property = Reflection_Property::getInstanceOf($this->class_name, $path);
				$property_link[$path] = $property->getAnnotation("link")->value;
				$property_class_name[$path] = $property->getType()->getElementTypeAsString();
			}
			$i = strrpos($property_path, ".");
			$property_name = substr($property_path, ($i === false) ? 0 : ($i + 1));
			$property_path = substr($property_path, 0, $i);
			$property_column[$property_path][$property_name] = $icol;
		}
		foreach (array_keys($property_column) as $property_path) if ($property_path) {
			$path = "";
			foreach (explode(".", $property_path) as $property_name) {
				if (!isset($property_column[$path][$property_name])) {
					$property_column[$path][$property_name] = $path . ($path ? "." : "") . $property_name;
				}
				$path .= ($path ? "." : "") . $property_name;
			}
		}
		unset($array[key($array)]);

		// --- for each class
		foreach ($this->sortedClasses() as $class) {
echo "<h2>class $class->class_name</h2><pre>" . print_r($class, true) . "</pre>";
			$property_path = implode(".", $class->property_path);
			$property_cols = $property_column[$property_path];
			reset($array);
//echo "property_cols for $property_path = " . print_r($property_cols, true) . "<br>";
			foreach ($array as $irow => $row) {
echo "<p>- line " . print_r($row, true) . "<br>";
				$empty_object = true;
				$search = array();
				foreach (array_keys($class->identify_properties) as $property_name) {
					$value = $row[$property_cols[$property_name]];
					$search[$property_name] = $value;
					$empty_object = $empty_object && empty($value);
				}
				if (in_array($property_link[$property_path], array("Collection", "Map"))) {
					$object = $empty_object ? null : $search;
echo "store search object $class->class_name $property_path = " . print_r($object, true) . "<br>";
				}
				else {
echo "search $class->class_name " . ($empty_object ? " empty object" : ("<pre>" . print_r($search, true) . "</pre>")) . "<br>";
					$found = $empty_object ? null : Dao::search($search, $class->class_name);
					echo "found " . (isset($found) ? print_r($found, true) : "empty value => no object") . "<br>";
					if (!isset($found)) {
						$object = null;
					}
					elseif (count($found) == 1) {
						$object = reset($found);
						$do_write = false;
						foreach (array_keys($class->write_properties) as $property_name) {
							if ($object->$property_name !== $row[$property_cols[$property_name]]) {
								$object->$property_name = $row[$property_cols[$property_name]];
								$do_write = true;
							}
						}
						if ($do_write) {
	echo "<h2>WILL UPDATE $class->class_name $property_path</h2>" . print_r($object, true) . "<p>";
							Dao::write($object);
						}
					}
					elseif (!count($found)) {
						if ($class->object_not_found_behaviour === "create_new_value") {
							$object = Builder::create($class->class_name);
							foreach (array_keys($class->identify_properties) as $property_name) {
								$object->$property_name = $row[$property_cols[$property_name]];
							}
							foreach (array_keys($class->write_properties) as $property_name) {
								$object->$property_name = $row[$property_cols[$property_name]];
							}
	echo "<h2>WILL CREATE $class->class_name $property_path</h2>" . print_r($object, true) . "<p>";
							Dao::write($object);
						}
						elseif ($class->object_not_found_behaviour === "tell_it_and_stop_import") {
							trigger_error(
								"Not found " . $class->class_name . " " . print_r($search, true), E_USER_ERROR
							);
							$object = null;
						}
						else {
	echo "<h2>WILL IGNORE $class->class_name $property_path</h2>";
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
echo "- result line " . print_r($array[$irow], true);
			}
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

}
