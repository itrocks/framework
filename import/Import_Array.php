<?php
namespace ITRocks\Framework\Import;

use Exception;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Import;
use ITRocks\Framework\Import\Settings\Import_Class;
use ITRocks\Framework\Import\Settings\Import_Property;
use ITRocks\Framework\Import\Settings\Import_Settings;
use ITRocks\Framework\Locale;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;
use ITRocks\Framework\View\View_Exception;
use ITRocks\Framework\Widget\Data_List_Setting\Data_List_Settings;
use ReflectionException;

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
	protected $properties_column;

	//------------------------------------------------------------------------------ $properties_link
	/**
	 * values of each property's @link annotation
	 *
	 * @var string[] string[string $property_path]
	 */
	protected $properties_link;

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
	 * @param $this_constants string[] $value = string[$property_path]
	 * @param $array          array    $value = string[integer $row_number][integer $column_number]
	 */
	protected static function addConstantsToArray(array $this_constants, array &$array)
	{
		if ($this_constants) {
			$constants    = [];
			$column_first = $column_number = count(current($array));
			// $this_constants['property*.path'] = 'value'
			// $constants[$column_number] = 'value'
			foreach ($this_constants as $value) {
				$constants[$column_number++] = $value;
			}
			$properties_key = key($array);
			$column_number  = $column_first;
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
	 * @throws ReflectionException
	 */
	protected function createArrayReference($class_name, array $search = null)
	{
		$array      = (isset($search)) ? [Builder::fromArray($class_name, $search)] : null;
		$class      = new Link_Class($class_name);
		$link_class = Class_\Link_Annotation::of($class)->value;
		if ($link_class) {
			$object                  = reset($array);
			$link_search             = Builder::create($link_class);
			$composite_property_name = $class->getCompositeProperty()->name;
			foreach (array_keys($class->getLinkedProperties()) as $property_name) {
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
	public static function getClassNameFromArray(array &$array)
	{
		$row              = reset($array);
		$array_class_name = reset($row);
		return self::getClassNameFromValue(
			(
				$array_class_name
				&& (ctype_upper($array_class_name[0]))
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
		return isset($value) ? Builder::className($value) : null;
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
	public static function getConstantsFromArray(array &$array)
	{
		$constants = [];
		$row       = self::getClassNameFromArray($array) ? next($array) : current($array);
		while ($row && (count($row) > 1) && ($row[1] == '=')) {
			$constants[$row[0]] = isset($row[2]) ? $row[2] : '';
			$row                = next($array);
		}
		return $constants;
	}

	//---------------------------------------------------------------------------------- getException
	/**
	 * Returns an import exception object containing the $feature_name view result
	 *
	 * @param $feature_name string
	 * @param $parameters   array
	 * @return View_Exception
	 * @throws ReflectionException
	 */
	public static function getException($feature_name, array $parameters)
	{
		$parameters[Parameter::AS_WIDGET] = true;
		if (isset($parameters['class'])) {
			$parameters['display'] = Names::classToDisplay($parameters['class']->class_name);
		}
		$parameters[Template::TEMPLATE] = 'import' . ucfirst($feature_name) . 'Error';
		return new View_Exception(
			View::run($parameters, [], [], Import::class, Feature::F_IMPORT)
		);
	}

	//---------------------------------------------------------------------------- getPropertiesAlias
	/**
	 * Gets properties alias from current list settings
	 *
	 * TODO user must place himself into the list settings matching the import, should search it
	 *
	 * @param $class_name string
	 * @return string[] $property_alias = string[string $property_name]
	 */
	public static function getPropertiesAlias($class_name)
	{
		$list_settings    = Data_List_Settings::current($class_name);
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
	 * @param $array      array $value = string[integer $row_number][integer $column_number]
	 * @param $class_name string class name : if set, will use current list settings properties alias
	 * @return string[] $property_path = string[integer $column_number]
	 * @throws Exception
	 */
	public static function getPropertiesFromArray(array &$array, $class_name = null)
	{
		$use_reverse_translation = Locale::current() ? true : false;
		$properties_alias        = isset($class_name) ? self::getPropertiesAlias($class_name) : null;
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

	//-------------------------------------------------------------------- getPropertiesLinkAndColumn
	/**
	 * @param $class_name      string Main class name
	 * @param $properties_path string[] $property_path = string[integer $column_number]
	 * @return array [$property_link = string[string $property_path], $property_column = string[string $property_path]]
	 */
	protected static function getPropertiesLinkAndColumn($class_name, array $properties_path)
	{
		$properties_link   = ['' => Link_Annotation::OBJECT];
		$properties_column = [];
		foreach ($properties_path as $col_number => $property_path) {
			$property_path = str_replace('*', '', $property_path);
			$path          = '';
			foreach (explode(DOT, $property_path) as $property_name) {
				$path .= ($path ? DOT : '') . $property_name;
				try {
					$property               = new Reflection_Property($class_name, $path);
					$properties_link[$path] = Link_Annotation::of($property)->value;
				}
				catch (ReflectionException $exception) {
					$properties_link[$path] = '';
				}
			}
			$i             = strrpos($property_path, DOT);
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
	protected function getSearchObject(
		array $row, array $identify_properties, array $class_properties_column
	) {
		$empty_object = true;
		$search       = [];
		foreach ($identify_properties as $identify_property) {
			$property                         = $identify_property->toProperty();
			$value                            = $row[$class_properties_column[$identify_property->name]];
			$search[$identify_property->name] = Loc::propertyToIso($property, $value);
			$empty_object                     = $empty_object && empty($value);
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
	 * @throws ReflectionException
	 * @throws View_Exception
	 * @throws Exception
	 */
	public function importArray(array &$array)
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
	 * @throws ReflectionException
	 * @throws View_Exception
	 */
	protected function importArrayClass(Import_Class $class, array &$array)
	{
		$property_path = join(DOT, $class->property_path);
		/** @var $class_properties_column integer[] key is the property name of the current class */
		$class_properties_column = $this->properties_column[$property_path];
		$simulation              = $this->simulation;
		while (($row = next($array)) && (!$this->simulation || $simulation)) {
			$search = $this->getSearchObject(
				$row, $class->identify_properties, $class_properties_column
			);
			$object = in_array(
				$this->properties_link[$property_path],
				[Link_Annotation::COLLECTION, Link_Annotation::MAP]
			)
				? $this->createArrayReference($class->class_name, $search)
				: $this->importSearchObject($search, $row, $class, $class_properties_column);
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
	 * @throws View_Exception
	 * @throws ReflectionException
	 */
	public function importSearchObject(
		$search, array $row, Import_Class $class, array $class_properties_column
	) {
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
				$object = null;
				throw $this->getException('notFound', ['class' => $class, 'search' => $search]);
			}
			else {
				$object = null;
			}
		}
		else {
			$object = null;
			throw $this->getException(
				'multipleResults', ['class' => $class, 'found' => $found, 'search' => $search]
			);
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
	 * @throws Exception
	 */
	public static function propertyPathOf(
		$class_name, $property_path, $use_reverse_translation = false, array $properties_alias = null
	) {
		if (isset($properties_alias) && isset($properties_alias[$property_path])) {
			$property_path = $properties_alias[$property_path];
		}
		elseif ($use_reverse_translation) {
			$property_class_name = $class_name;
			$property_names      = [];
			foreach (explode(DOT, $property_path) as $property_name) {
				if ($asterisk = (substr($property_name, -1) == '*')) {
					$property_name = substr($property_name, 0, -1);
				}
				$property      = null;
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
						// TODO do not catch without at least reporting the problem
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

	//------------------------------------------------------------------------------------- sameArray
	/**
	 * Returns true if $array1 and $array2 contain the same data
	 *
	 * @param $array1 array
	 * @param $array2 array
	 * @return boolean
	 */
	protected function sameArray(array $array1, array $array2)
	{
		if (count($array1) === count($array2)) {
			foreach ($array1 as $value1) {
				$found = false;
				foreach ($array2 as $key2 => $value2) {
					if ($this->sameElement($value1, $value2)) {
						$found = true;
						unset($array2[$key2]);
						break;
					}
				}
				if (!$found) {
					return false;
				}
			}
		}
		else {
			return false;
		}
		return true;
	}

	//----------------------------------------------------------------------------------- sameElement
	/**
	 * Returns true if $value1 and $value2 are the same element (value, object, array)
	 *
	 * @param $value1 mixed
	 * @param $value2 mixed
	 * @return boolean
	 */
	protected function sameElement($value1, $value2)
	{
		return
			(is_array($value1) && is_array($value2) && $this->sameArray($value1, $value2))
			|| (is_object($value1) && is_object($value2) && $this->sameObject($value1, $value2))
			|| (!is_array($value1) && !is_object($value1) && (strval($value1) === strval($value2)));
	}

	//------------------------------------------------------------------------------------ sameObject
	/**
	 * Returns true if $object1 and $object2 are the same into data store
	 *
	 * @param $object1 object
	 * @param $object2 object
	 * @return boolean
	 */
	protected function sameObject($object1, $object2)
	{
		return ($object1 instanceof Date_Time)
			? $this->sameArray(get_object_vars($object1), get_object_vars($object2))
			: Dao::is($object1, $object2);
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
	 * @param $class      Import_Class
	 * @param $search     string[]
	 * @param $class_name string
	 */
	protected function simulateSearch(Import_Class $class, array $search, $class_name)
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
	protected function sortedClasses()
	{
		uksort($this->settings->classes, function ($class_path_1, $class_path_2) {
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
	 * @throws ReflectionException
	 */
	protected function updateExistingObject(
		$object, $row, Import_Class $class, array $class_properties_column
	) {
		$before          = Reflection_Class::getObjectVars($object);
		$only_properties = [];
		foreach (array_keys($class->write_properties) as $property_name) {
			$value = $row[$class_properties_column[$property_name]];
			if (isset($class->properties[$property_name])) {
				$value = Loc::propertyToIso($class->properties[$property_name], $value);
			}
			if (!$this->sameElement($object->$property_name, $value)) {
				$object->$property_name = $value;
				$only_properties[]      = $property_name;
				unset($before[$property_name]);
			}
		}
		if ($only_properties) {
			foreach ($before as $property_name => $old_value) {
				if (!$this->sameElement($object->$property_name, $old_value)) {
					$only_properties[] = $property_name;
				}
			}
			if ($this->simulation) {
				$this->simulateUpdate($class, $object);
			}
			Dao::write($object, Dao::only($only_properties));
		}
		return $object;
	}

	//-------------------------------------------------------------------------------- writeNewObject
	/**
	 * @param $row                     array
	 * @param $class                   Import_Class
	 * @param $class_properties_column integer[]|string[]
	 * @return object
	 * @throws ReflectionException
	 */
	protected function writeNewObject(array $row, Import_Class $class, array $class_properties_column)
	{
		$object          = Builder::create($class->class_name);
		$only_properties = [];
		foreach (array_keys($class->identify_properties) as $property_name) {
			$value = $row[$class_properties_column[$property_name]];
			if (isset($class->properties[$property_name])) {
				$value = Loc::propertyToIso($class->properties[$property_name], $value);
			}
			elseif (isset($class->identify_properties[$property_name])) {
				$identify_property = $class->identify_properties[$property_name];
				$value             = Loc::propertyToIso($identify_property->toProperty(), $value);
			}
			$object->$property_name = $value;
			$only_properties[]      = $property_name;
		}
		foreach (array_keys($class->write_properties) as $property_name) {
			$value = $row[$class_properties_column[$property_name]];
			if (isset($class->properties[$property_name])) {
				$value = Loc::propertyToIso($class->properties[$property_name], $value);
			}
			$object->$property_name = $value;
			$only_properties[]      = $property_name;
		}
		if ($this->simulation) {
			$this->simulateNew($class, $object);
		}
		// class with @link annotation will crash without restricting the properties here :
		$is_link_class = Link_Annotation::of(new Link_Class($class->class_name))->value;
		Dao::write($object, $is_link_class ? Dao::only($only_properties) : []);
		return $object;
	}

}
