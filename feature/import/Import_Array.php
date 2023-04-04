<?php
namespace ITRocks\Framework\Feature\Import;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Import;
use ITRocks\Framework\Feature\Import\Settings\Behaviour;
use ITRocks\Framework\Feature\Import\Settings\Import_Class;
use ITRocks\Framework\Feature\Import\Settings\Import_Property;
use ITRocks\Framework\Feature\Import\Settings\Import_Settings;
use ITRocks\Framework\Feature\List_Setting;
use ITRocks\Framework\Locale;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_\Representative;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Stringable;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;
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
	public string $class_name = '';

	//---------------------------------------------------------------------------- $properties_column
	/**
	 * @var array $property_column_number integer[string $property_path][string $property_name]
	 */
	protected array $properties_column;

	//------------------------------------------------------------------------------ $properties_link
	/**
	 * values of each property's @link annotation
	 *
	 * @var string[] string[string $property_path]
	 */
	protected array $properties_link;

	//------------------------------------------------------------------------------------- $settings
	/**
	 * @var Import_Settings
	 */
	public Import_Settings $settings;

	//----------------------------------------------------------------------------------- $simulation
	/**
	 * Simulation pass : false or number of rows to simulate
	 *
	 * @var integer|boolean
	 */
	public bool|int $simulation = false;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $settings   Import_Settings|null
	 * @param $class_name string|null
	 */
	public function __construct(Import_Settings $settings = null, string $class_name = null)
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
	protected static function addConstantsToArray(array $this_constants, array &$array) : void
	{
		if (!$this_constants) {
			return;
		}
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
		while (next($array)) {
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

	//-------------------------------------------------------------------------- createArrayReference
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @param $search     array|null
	 * @return array
	 */
	protected function createArrayReference(string $class_name, array $search = null) : array
	{
		/** @noinspection PhpUnhandledExceptionInspection valid $class_name */
		$array = (isset($search)) ? [Builder::fromArray($class_name, $search)] : null;
		/** @noinspection PhpUnhandledExceptionInspection valid $class_name */
		$class      = new Link_Class($class_name);
		$link_class = Class_\Link_Annotation::of($class)->value;
		if ($link_class) {
			$object = reset($array);
			/** @noinspection PhpUnhandledExceptionInspection valid @link class_name */
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
	public static function getClassNameFromArray(array &$array) : string
	{
		$row              = reset($array);
		$array_class_name = reset($row);
		return self::getClassNameFromValue(
			(
				$array_class_name
				&& (ctype_upper($array_class_name[0]))
				&& ((count($row) === 1) || !$row[1])
			)
				? $array_class_name
				: ''
		);
	}

	//------------------------------------------------------------------------- getClassNameFromValue
	/**
	 * @param $value string class name taken from the import array
	 * @return string class name (built and with added namespace, if needed)
	 */
	public static function getClassNameFromValue(string $value) : string
	{
		return ($value === '') ? '' : Builder::className($value);
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
	public static function getConstantsFromArray(array &$array) : array
	{
		$constants = [];
		$row       = self::getClassNameFromArray($array) ? next($array) : current($array);
		while ($row && (count($row) > 1) && ($row[1] === '=')) {
			$constants[$row[0]] = $row[2] ?? '';
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
	 * @return Import_Exception
	 */
	public static function getException(string $feature_name, array $parameters) : Import_Exception
	{
		$parameters[Parameter::AS_WIDGET] = true;
		if (isset($parameters['class'])) {
			$parameters['display'] = Names::classToDisplay($parameters['class']->class_name);
		}
		$parameters[Template::TEMPLATE] = 'import' . ucfirst($feature_name) . 'Error';
		return new Import_Exception(
			View::run($parameters, [], [], Import::class, Feature::F_IMPORT)
		);
	}

	//---------------------------------------------------------------------------- getPropertiesAlias
	/**
	 * Gets properties alias from current list settings
	 *
	 * @param $class_name string
	 * @return string[] $property_alias = string[string $property_name]
	 */
	public static function getPropertiesAlias(string $class_name) : array
	{
		$list_settings    = List_Setting\Set::current($class_name);
		$properties_alias = [];
		foreach ($list_settings->properties as $property) {
			$properties_alias[Names::displayToProperty($property->display)] = $property->path;
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
	 * The cursor on $array is set to the row containing the property paths.
	 *
	 * @param $array      array $value = string[integer $row_number][integer $column_number]
	 * @param $class_name string class name : if set, will use current list settings properties alias
	 * @return string[] $property_path = string[integer $column_number]
	 */
	public static function getPropertiesFromArray(array &$array, string $class_name = '') : array
	{
		$use_reverse_translation = (bool)Locale::current();
		$properties_alias        = $class_name ? self::getPropertiesAlias($class_name) : [];
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
	protected static function getPropertiesLinkAndColumn(string $class_name, array $properties_path)
		: array
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
				catch (ReflectionException) {
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
	) : array
	{
		$empty_object = true;
		$search       = [];
		foreach ($identify_properties as $identify_property) {
			$property                         = $identify_property->toProperty();
			$value                            = $row[$class_properties_column[$identify_property->name]];
			$search[$identify_property->name] = $this->propertyToIso($property, $value);
			$empty_object                     = $empty_object && empty($value);
		}
		return $empty_object ? [] : $search;
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
	 * @throws Import_Exception
	 */
	public function importArray(array &$array) : void
	{
		Dao::begin();
		$class_name = self::getClassNameFromArray($array) ?: $this->class_name;
		if ($class_name) {
			$this->class_name = $class_name;
		}
		[$this->properties_link, $this->properties_column] = self::getPropertiesLinkAndColumn(
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
	 * @throws Import_Exception
	 */
	protected function importArrayClass(Import_Class $class, array &$array) : void
	{
		$property_path = join(DOT, $class->property_path);
		/** @var $class_properties_column integer[] key is the property name of the current class */
		$class_properties_column = $this->properties_column[$property_path];
		$simulation              = $this->simulation;
		while (($row = next($array)) && (!$this->simulation || $simulation)) {
			$search = $this->getSearchObject(
				$row, $class->identify_properties, $class_properties_column
			);
			switch ($this->properties_link[$property_path]) {
				case Link_Annotation::COLLECTION:
					throw new Import_Exception(
						'Component objects import not implemented (' . $property_path . ')'
					);
				case Link_Annotation::MAP:
					$object = $this->importSearchObject($search, $row, $class, $class_properties_column);
					$array[key($array)][$property_path][] = $object;
					break;
				default:
					$object = $this->importSearchObject($search, $row, $class, $class_properties_column);
					$array[key($array)][$property_path] = $object;
			}
			$simulation --;
		}
	}

	//---------------------------------------------------------------------------- importSearchObject
	/**
	 * @param $search                  array
	 * @param $row                     array
	 * @param $class                   Import_Class
	 * @param $class_properties_column integer[]
	 * @return ?object
	 * @throws Import_Exception
	 */
	public function importSearchObject(
		array $search, array $row, Import_Class $class, array $class_properties_column
	) : ?object
	{
		if ($this->simulation && $search) {
			$this->simulateSearch($class, $search, $class->class_name);
		}
		$found = $search ? Dao::search($search, $class->class_name) : null;
		if (!isset($found)) {
			$object = null;
		}
		elseif (count($found) === 1) {
			$object = $this->updateExistingObject(reset($found), $row, $class, $class_properties_column);
		}
		elseif (!count($found)) {
			if ($class->object_not_found_behaviour === Behaviour::CREATE_NEW_VALUE) {
				$object = $this->writeNewObject($row, $class, $class_properties_column);
			}
			elseif ($class->object_not_found_behaviour === Behaviour::TELL_IT_AND_STOP_IMPORT) {
				throw static::getException('notFound', ['class' => $class, 'search' => $search]);
			}
			else {
				$object = null;
			}
		}
		else {
			throw static::getException(
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
	 */
	public static function propertyPathOf(
		string $class_name, string $property_path, bool $use_reverse_translation = false,
		array $properties_alias = []
	) : string
	{
		if (isset($properties_alias[$property_path])) {
			$property_path = $properties_alias[$property_path];
		}
		if (!$use_reverse_translation) {
			return $property_path;
		}
		$property_class_name = $class_name;
		$property_names      = [];
		foreach (explode(DOT, $property_path) as $property_name) {
			if ($asterisk = str_ends_with($property_name, '*')) {
				$property_name = substr($property_name, 0, -1);
			}
			$property      = null;
			$property_name = Names::displayToProperty($property_name);
			try {
				$property = new Reflection_Property($property_class_name, $property_name);
			}
			catch (ReflectionException) {
				$source_property_names = Loc::rtr($property_name, $property_class_name);
				if (!is_array($source_property_names)) {
					$source_property_names = [$source_property_names];
				}
				foreach ($source_property_names as $source_property_name) {
					$translated_property_name = Names::displayToProperty($source_property_name);
					try {
						$property = new Reflection_Property($property_class_name, $translated_property_name);
						$property_name = $translated_property_name;
						break;
					}
					catch (ReflectionException) {
						// TODO do not catch without at least reporting the problem
					}
				}
			}
			$property_names[] = $property_name . ($asterisk ? '*' : '');
			if (!$property) {
				break;
			}
			$property_class_name = $property->getType()->getElementTypeAsString();
		}
		return join(DOT, $property_names);
	}

	//--------------------------------------------------------------------------------- propertyToIso
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $property Reflection_Property
	 * @param $value    mixed
	 * @return mixed
	 */
	protected function propertyToIso(Reflection_Property $property, mixed $value) : mixed
	{
		$value = Loc::propertyToIso($property, $value);
		if ($property->getType()->isClass() && !is_object($value) && !is_null($value)) {
			if (trim($value) === '') {
				return null;
			}
			$class_name = $property->getType()->getElementTypeAsString();
			if (isA($class_name, Stringable::class)) {
				/** @noinspection PhpUndefinedMethodInspection $class_name */
				/** @see Stringable::fromString */
				$value = $class_name::fromString($value);
				if ($found = Dao::searchOne($value)) {
					$value = $found;
				}
			}
			else {
				/** @noinspection PhpUnhandledExceptionInspection must be valid */
				$class = new Reflection_Class($class_name);
				$representative_property_names = Representative::of($class)->values;
				$search = [];
				$values = explode(' ', $value, count($representative_property_names));
				foreach ($representative_property_names as $key => $property_name) {
					/** @noinspection PhpUnhandledExceptionInspection must be valid */
					$property               = $class->getProperty($property_name);
					$search[$property_name] = $this->propertyToIso($property, $values[$key] ?? '');
				}
				$object = Dao::searchOne($search, $class_name);
				if (!$object) {
					/** @noinspection PhpUnhandledExceptionInspection must be valid */
					$object = $class->newInstance();
					foreach ($search as $property_name => $value) {
						$object->$property_name = $value;
					}
				}
				$value = $object;
			}
		}
		return $value;
	}

	//------------------------------------------------------------------------------------- sameArray
	/**
	 * Returns true if $array1 and $array2 contain the same data
	 *
	 * @param $array1 array
	 * @param $array2 array
	 * @return boolean
	 */
	protected function sameArray(array $array1, array $array2) : bool
	{
		if (count($array1) !== count($array2)) {
			return false;
		}
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
	protected function sameElement(mixed $value1, mixed $value2) : bool
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
	protected function sameObject(object $object1, object $object2) : bool
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
	protected function simulateNew(
		/** @noinspection PhpUnusedParameterInspection */ Import_Class $class, object $object
	) : void
	{
		echo '- write new ' . print_r($object, true);
	}

	//-------------------------------------------------------------------------------- simulateSearch
	/**
	 * @param $class      Import_Class
	 * @param $search     string[]
	 * @param $class_name string
	 */
	protected function simulateSearch(
		/** @noinspection PhpUnusedParameterInspection */ Import_Class $class,
		array $search,
		string $class_name
	) : void
	{
		echo '- search ' . $class_name . ' = ' . print_r($search, true) . BR;
	}

	//-------------------------------------------------------------------------------- simulateUpdate
	/**
	 * @param $class  Import_Class
	 * @param $object object
	 */
	protected function simulateUpdate(
		/** @noinspection PhpUnusedParameterInspection */ Import_Class $class, object $object
	) : void
	{
		echo '- update ' . print_r($object, true) . BR;
	}

	//--------------------------------------------------------------------------------- sortedClasses
	/**
	 * Sorts classes and returns a class list, starting from the one which has the more depth
	 *
	 * @return Import_Class[]
	 */
	protected function sortedClasses() : array
	{
		uksort($this->settings->classes, function(string $class_path_1, string $class_path_2) : int {
			return $class_path_1
				? (substr_count($class_path_2, DOT) - substr_count($class_path_1, DOT))
				: 1;
		});
		return $this->settings->classes;
	}

	//-------------------------------------------------------------------------- updateExistingObject
	/**
	 * @param $object                  T
	 * @param $row                     array
	 * @param $class                   Import_Class
	 * @param $class_properties_column integer[]|string[]
	 * @return T
	 * @template T
	 */
	protected function updateExistingObject(
		object $object, array $row, Import_Class $class, array $class_properties_column
	) : object
	{
		// tested for optimization reason : avoid getObjectVars if nothing to do with it
		if ($class->write_properties) {
			$before          = Reflection_Class::getObjectVars($object);
			$only_properties = [];
			foreach (array_keys($class->write_properties) as $property_name) {
				$value = $row[$class_properties_column[$property_name]];
				if (isset($class->properties[$property_name])) {
					$value = $this->propertyToISo($class->properties[$property_name], $value);
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
		}
		return $object;
	}

	//-------------------------------------------------------------------------------- writeNewObject
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $row                     array
	 * @param $class                   Import_Class
	 * @param $class_properties_column integer[]|string[]
	 * @return object
	 */
	protected function writeNewObject(array $row, Import_Class $class, array $class_properties_column)
		: object
	{
		/** @noinspection PhpUnhandledExceptionInspection import class must be valid */
		$object          = Builder::create($class->class_name);
		$only_properties = [];
		foreach (array_keys($class->identify_properties) as $property_name) {
			$value = $row[$class_properties_column[$property_name]];
			if (isset($class->properties[$property_name])) {
				$value = $this->propertyToIso($class->properties[$property_name], $value);
			}
			elseif (isset($class->identify_properties[$property_name])) {
				$identify_property = $class->identify_properties[$property_name];
				$value             = $this->propertyToIso($identify_property->toProperty(), $value);
			}
			$object->$property_name = $value;
			$only_properties[]      = $property_name;
		}
		foreach (array_keys($class->write_properties) as $property_name) {
			$value = $row[$class_properties_column[$property_name]];
			if (isset($class->properties[$property_name])) {
				$value = $this->propertyToIso($class->properties[$property_name], $value);
			}
			$object->$property_name = $value;
			$only_properties[]      = $property_name;
		}
		if ($this->simulation) {
			$this->simulateNew($class, $object);
		}
		// class with @link annotation will crash without restricting the properties here :
		/** @noinspection PhpUnhandledExceptionInspection import class must be valid */
		$is_link_class = Class_\Link_Annotation::of(new Link_Class($class->class_name))->value;
		Dao::write($object, $is_link_class ? Dao::only($only_properties) : []);
		return $object;
	}

}
