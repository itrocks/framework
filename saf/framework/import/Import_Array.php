<?php
namespace SAF\Framework\Import;

use ReflectionException;
use SAF\Framework\Builder;
use SAF\Framework\Dao;
use SAF\Framework\Import\Settings\Import_Class;
use SAF\Framework\Import\Settings\Import_Property;
use SAF\Framework\Import\Settings\Import_Settings;
use SAF\Framework\Locale;
use SAF\Framework\Locale\Loc;
use SAF\Framework\Reflection\Link_Class;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Tools\Names;
use SAF\Framework\Tools\Namespaces;
use SAF\Framework\Widget\List_\List_Settings;

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
	 * @var array $property_column_number integer[string $property_path][string $property_name]
	 */
	private $properties_column;

	//------------------------------------------------------------------------------ $properties_link
	/**
	 * values of each property's @link annotation
	 *
	 * @var string[] string[string $property_path]
	 */
	private $properties_link;

	//------------------------------------------------------------------------------------- $settings
	/**
	 * @var Import_Settings
	 */
	public $settings;

	//----------------------------------------------------------------------------------- $simulation
	/**
	 * Simulation pass : false or number of rows to simulate
	 *
	 * @var integer|boolean
	 */
	public $simulation = false;

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
	 * @param $this_constants $value = string[$property_path]
	 * @param $array          $value = string[integer $row_number][integer $column_number]
	 */
	private static function addConstantsToArray($this_constants, &$array)
	{
		if ($this_constants) {
			$constants = [];
			$column_first = $column_number = count(current($array));
			// $this_constants['property*.path'] = 'value'
			// $constants[$column_number] = 'value'
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
		$array = (isset($search)) ? [Builder::fromArray($class_name, $search)] : null;
		$class = new Link_Class($class_name);
		$link_class = $class->getAnnotation('link')->value;
		if ($link_class) {
			$object = reset($array);
			$link_search = Builder::create($link_class);
			$composite_property_name = $class->getCompositeProperty()->name;
			foreach (array_keys($class->getLinkProperties()) as $property_name) {
				if (isset($search[$property_name])) {
					$link_search->$property_name = $search[$property_name];
				}
			}
			$object->$composite_property_name = Dao::searchOne($link_search) ?: $link_search;
		}
		return $array;
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
	 * @param $array array $value = string[$row_number][$column_number]
	 * @return string
	 */
	public static function getClassNameFromArray(&$array)
	{
		$row = reset($array);
		$array_class_name = reset($row);
		return self::getClassNameFromValue(
			(
				$array_class_name
				&& ($array_class_name[0] === strtoupper($array_class_name[0]))
				&& ((count($row) == 1) || !$row[1])
			)
			? $array_class_name
			: null
		);
	}

	//------------------------------------------------------------------------- getClassNameFromValue
	/**
	 * @param $value  string class name taken from the import array
	 * @return string class name (built and with added namespace, if needed)
	 */
	public static function getClassNameFromValue($value)
	{
		return isset($value) ? Builder::className(Namespaces::fullClassName($value)) : null;
	}

	//------------------------------------------------------------------------- getConstantsFromArray
	/**
	 * Gets the constants properties path and value from array
	 *
	 * The cursor on $array is set to the row containing the definitive properties path
	 *
	 * @param $array array $value = string[$row_number][$column_number]
	 * @return string[] $fixed_value_for_import = string[string $property_path]
	 */
	public static function getConstantsFromArray(&$array)
	{
		$constants = [];
		$row = self::getClassNameFromArray($array) ? next($array) : current($array);
		while ($row && (count($row) > 1) && ($row[1] == '=')) {
			$constants[$row[0]] = isset($row[2]) ? $row[2] : '';
			$row = next($array);
		}
		return $constants;
	}

	//---------------------------------------------------------------------------- getPropertiesAlias
	/**
	 * Gets properties alias from current list settings
	 *
	 * @todo user must place himself into the list settings matching the import, should search it
	 * @param $class_name string
	 * @return string[] $property_alias = string[string $property_name]
	 */
	public static function getPropertiesAlias($class_name)
	{
		$list_settings = List_Settings::current($class_name);
		$properties_alias = [];
		foreach ($list_settings->properties_title as $property_path => $property_title) {
			$properties_alias[Names::displayToProperty($property_title)] = $property_path;
		}
		return $properties_alias;
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
	 * @param $array      $value = string[integer $row_number][integer $column_number]
	 * @param $class_name string class name : if set, will use current list settings properties alias
	 * @return string[] $property_path = string[integer $column_number]
	 */
	public static function getPropertiesFromArray(&$array, $class_name = null)
	{
		$use_reverse_translation = Locale::current() ? true : false;
		$properties_alias = isset($class_name) ? self::getPropertiesAlias($class_name) : null;
		self::addConstantsToArray(self::getConstantsFromArray($array), $array);
		$properties = [];
		foreach (current($array) as $column_number => $property_path) {
			if ($property_path) {
				$properties[$column_number] = self::propertyPathOf(
					$class_name, $property_path, $use_reverse_translation, $properties_alias
				);
			}
		}
		return $properties;
	}

	//---------------------------------------------------------------------- getPropertyLinkAndColumn
	/**
	 * @param $class_name      string Main class name
	 * @param $properties_path string[] $property_path = string[integer $column_number]
	 * @return array [$property_link = string[string $property_path], $property_column = string[string $property_path]]
	 */
	private static function getPropertiesLinkAndColumn($class_name, $properties_path)
	{
		$properties_link = ['' => 'Object'];
		$properties_column = [];
		foreach ($properties_path as $col_number => $property_path) {
			$property_path = str_replace('*', '', $property_path);
			$path = '';
			foreach (explode(DOT, $property_path) as $property_name) {
				$path .= ($path ? DOT : '') . $property_name;
				try {
					$property = new Reflection_Property($class_name, $path);
					$properties_link[$path] = $property->getAnnotation('link')->value;
				}
				catch (ReflectionException $exception) {
					$properties_link[$path] = '';
				}
			}
			$i = strrpos($property_path, DOT);
			$property_name = substr($property_path, ($i === false) ? 0 : ($i + 1));
			$property_path = substr($property_path, 0, $i);
			$properties_column[$property_path][$property_name] = $col_number;
		}
		foreach (array_keys($properties_column) as $property_path) if ($property_path) {
			$path = '';
			foreach (explode(DOT, $property_path) as $property_name) {
				if (!isset($properties_column[$path][$property_name])) {
					$properties_column[$path][$property_name] = $path . ($path ? DOT : '') . $property_name;
				}
				$path .= ($path ? DOT : '') . $property_name;
			}
		}
		return [$properties_link, $properties_column];
	}

	//------------------------------------------------------------------------------- getSearchObject
	/**
	 * @param $row                     string[] $value = string[integer $column_number]
	 * @param $identify_properties     Import_Property[] properties used to identify the read object
	 * @param $class_properties_column integer[] $column_number = integer[string $property_name]
	 * @return array search array for Dao::search() using $this->class_name
	 */
	private function getSearchObject($row, $identify_properties, $class_properties_column)
	{
		$empty_object = true;
		$search = [];
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
	 * Beware : if $array begins with a 'Class_Name' row, this first row will be removed !
	 * Beware : first row must contain property paths, and will be removed !
	 *
	 * @param $array array $value = string[$row_number][$column_number]
	 */
	public function importArray(&$array)
	{
		Dao::begin();
		$class_name = self::getClassNameFromArray($array) ?: $this->class_name;
		if (isset($class_name)) {
			$this->class_name = $class_name;
		}
		list($this->properties_link, $this->properties_column) = self::getPropertiesLinkAndColumn(
			$this->class_name, self::getPropertiesFromArray($array, $this->class_name)
		);
		$key = key($array);
		foreach ($this->sortedClasses() as $class) {
			reset($array);
			while (key($array) !== $key) {
				next($array);
			}
			$this->importArrayClass($class, $array);
		}
		if ($this->simulation) {
			Dao::rollback();
		}
		else {
			Dao::commit();
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
	 * @param $array array $value = string[integer $row_number][integer $column_number]
	 */
	private function importArrayClass(Import_Class $class, &$array)
	{
		$property_path = join(DOT, $class->property_path);
		/** @var $class_properties_column integer[] key is the property name of the current class */
		$class_properties_column = $this->properties_column[$property_path];
		$simulation = $this->simulation;
		while (($row = next($array)) && (!$this->simulation || $simulation)) {
			$search = $this->getSearchObject($row, $class->identify_properties, $class_properties_column);
			$object = (in_array($this->properties_link[$property_path], ['Collection', 'Map']))
				? $this->createArrayReference($class->class_name, $search)
				: $this->importSearchObject($search, $row, $class, $class_properties_column, $property_path);
			$array[key($array)][$property_path] = $object;
			$simulation --;
		}
	}

	//---------------------------------------------------------------------------- importSearchObject
	/**
	 * @param $search                  array|object
	 * @param $row                     array
	 * @param $class                   Import_Class
	 * @param $class_properties_column integer[]
	 * @return object
	 */
	public function importSearchObject($search, $row, Import_Class $class, $class_properties_column)
	{
		if ($this->simulation && isset($search)) {
			$this->simulateSearch($class, $search, $class->class_name);
		}
		$found = isset($search) ? Dao::search($search, $class->class_name) : null;
		if (!isset($found)) {
			$object = null;
		}
		elseif (count($found) == 1) {
			$object = $this->updateExistingObject(reset($found), $row, $class, $class_properties_column);
		}
		elseif (!count($found)) {
			if ($class->object_not_found_behaviour === 'create_new_value') {
				$object = $this->writeNewObject($row, $class, $class_properties_column);
			}
			elseif ($class->object_not_found_behaviour === 'tell_it_and_stop_import') {
				trigger_error(
					'Not found ' . $class->class_name . SP . print_r($search, true), E_USER_ERROR
				);
				$object = null;
			}
			else {
				$object = null;
			}
		}
		else {
			echo '<pre class="error">SEARCH = ' . print_r($search, true) . '</pre>';
			echo '<pre class="error">FOUND = ' . print_r($found, true) . '</pre>';
			trigger_error(
				'Multiple ' . Namespaces::shortClassName($class->class_name) . ' found', E_USER_ERROR
			);
			$object = null;
		}
		return $object;
	}

	//-------------------------------------------------------------------------------- propertyPathOf
	/**
	 * @param $class_name              string
	 * @param $property_path           string
	 * @param $use_reverse_translation boolean if true, will try reverse translation of property names
	 * @param $properties_alias        string[] $property_path = string[string $property_alias]
	 * @return string
	 */
	public static function propertyPathOf(
		$class_name, $property_path, $use_reverse_translation = false, $properties_alias = null
	) {
		if (isset($properties_alias) && isset($properties_alias[$property_path])) {
			$property_path = $properties_alias[$property_path];
		}
		elseif ($use_reverse_translation) {
			$property_class_name = $class_name;
			$property_names = [];
			foreach (explode(DOT, $property_path) as $property_name) {
				if ($asterisk = (substr($property_name, -1) == '*')) {
					$property_name = substr($property_name, 0, -1);
				}
				$property = null;
				$property_name = Names::displayToProperty($property_name);
				try {
					$property = new Reflection_Property($property_class_name, $property_name);
				}
				catch (ReflectionException $e) {
					$translated_property_name = Names::displayToProperty(Loc::rtr(
						$property_name, $property_class_name
					));
					try {
						$property = new Reflection_Property($property_class_name, $translated_property_name);
						$property_name = $translated_property_name;
					}
					catch (ReflectionException $e) {
					}
				}
				$property_names[] = $property_name . ($asterisk ? '*' : '');
				if (!isset($property)) {
					break;
				}
				$property_class_name = $property->getType()->getElementTypeAsString();
			}
			$property_path = join(DOT, $property_names);
		}
		return $property_path;
	}

	//----------------------------------------------------------------------------------- simulateNew
	/**
	 * @param $class  Import_Class
	 * @param $object object
	 */
	protected function simulateNew(Import_Class $class, $object)
	{
		echo '- write new ' . print_r($object, true);
	}

	//-------------------------------------------------------------------------------- simulateSearch
	/**
	 * @param $class  Import_Class
	 * @param $search     string[]
	 * @param $class_name string
	 */
	protected function simulateSearch(Import_Class $class, $search, $class_name)
	{
		echo '- search ' . $class_name . ' = ' . print_r($search, true) . BR;
	}

	//-------------------------------------------------------------------------------- simulateUpdate
	/**
	 * @param $class  Import_Class
	 * @param $object object
	 */
	protected function simulateUpdate(Import_Class $class, $object)
	{
		echo '- update ' . print_r($object, true) . BR;
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
			return ($class_path_1 == '')
				|| (substr_count($class_path_1, DOT) < substr_count($class_path_2, DOT));
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
			if ($this->simulation) {
				$this->simulateUpdate($class, $object);
			}
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
		if ($this->simulation) {
			$this->simulateNew($class, $object);
		}
		Dao::write($object);
		return $object;
	}

}
