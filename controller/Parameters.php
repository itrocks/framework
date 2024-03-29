<?php
namespace ITRocks\Framework\Controller;

use ITRocks\Framework\Application;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\List_\Selection;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper;
use ITRocks\Framework\Mapper\Object_Not_Found_Exception;
use ITRocks\Framework\Tools\Set;

/**
 * Controller parameters contains what objects are passed into the controller's URI
 */
class Parameters
{

	//-------------------------------------------------------------------------------------- $objects
	/**
	 * @var object[] keys are parameters names (ie object class short name)
	 */
	private array $objects = [];

	//----------------------------------------------------------------------------------- $parameters
	/**
	 * @var array keys are parameters names (ie object class short name)
	 */
	private array $parameters = [];

	//------------------------------------------------------------------------------------------ $uri
	/**
	 * The controller URI that is originator of these parameters (if set)
	 *
	 * @var Uri
	 */
	public Uri $uri;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $uri Uri|null
	 */
	public function __construct(Uri $uri = null)
	{
		if (isset($uri)) $this->uri = $uri;
	}

	//-------------------------------------------------------------------------------------- addValue
	/**
	 * Adds a parameter without name value
	 *
	 * @param $parameter_value mixed
	 * @return $this
	 */
	public function addValue(mixed $parameter_value) : static
	{
		$this->parameters[] = $parameter_value;
		return $this;
	}

	//-------------------------------------------------------------------------------------- contains
	/**
	 * Returns true if the parameters contain the value $value
	 *
	 * @param $value mixed the searched value
	 * @return boolean true if found, else false
	 */
	public function contains(mixed $value) : bool
	{
		return in_array($value, $this->parameters, true);
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * Gets parameters count
	 *
	 * @return integer
	 */
	public function count() : int
	{
		return count($this->parameters);
	}

	//-------------------------------------------------------------------------------- firstKeyObject
	/**
	 * @return array [$key mixed, $object object]
	 */
	protected function firstKeyObject() : array
	{
		foreach ($this->parameters as $key => $value) {
			if (is_object($value)) {
				return [$key, $value];
			}
			elseif (ctype_upper(substr($key, 0, 1)) && class_exists($key)) {
				return [$key, Dao::read($value, $key)];
			}
		}
		return [null, null];
	}

	//----------------------------------------------------------------------------------- firstObject
	/**
	 * @return ?object
	 */
	public function firstObject() : ?object
	{
		[,$value] = $this->firstKeyObject();
		return $value;
	}

	//--------------------------------------------------------------------------------- getMainObject
	/**
	 * Gets the main object from the parameters
	 *
	 * If no main object is set (eq first parameter is not an object), create it using class name.
	 * Beware : the created object will then automatically be added to the beginning
	 * of the parameters list.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name           class-string<T>|null
	 * @param $search_by_properties string[]
	 * @return T
	 * @template T
	 */
	public function getMainObject(string $class_name = null, array $search_by_properties = [])
		: object
	{
		reset($this->parameters);
		$object = $this->parameters ? $this->getObject(key($this->parameters)) : null;
		if (!$object || !is_object($object)) {
			if ($search_by_properties) {
				$object = $this->searchMainObject($class_name, $search_by_properties);
			}
			if ((!$object || !is_object($object)) && !$class_name) {
				$class_name = $this->uri->controller_name;
				if (is_a($class_name, Application::class, true)) {
					$object = call_user_func([$class_name, 'current']);
				}
			}
		}
		if (!$object || !is_object($object) || (isset($class_name) && !is_a($object, $class_name))) {
			/** @noinspection PhpUnhandledExceptionInspection class_exists */
			$object = $default_object ?? (
				(isset($class_name) && class_exists($class_name))
				? Builder::create($class_name)
				: Set::instantiate($class_name)
			);
			$this->parameters = array_merge([get_class($object) => $object], $this->parameters);
		}
		return $object;
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Gets URI parameter as an object
	 *
	 * Object is of class $parameter name, and is read from current data link using the parameter
	 * value as identifier.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameter_name class-string<T>|null
	 * @return mixed|T
	 * @template T
	 */
	public function getObject(string $parameter_name = null) : mixed
	{
		if (isset($this->objects[$parameter_name])) {
			// parameter is in cache
			$object = $this->objects[$parameter_name];
		}
		elseif (is_numeric($this->getRawParameter($parameter_name))) {
			$class_name = $this->uri->isClassName($parameter_name) ? $parameter_name : null;
			if ($class_name && class_exists($class_name)) {
				// object parameter
				$object = intval($this->getRawParameter($parameter_name));
				Mapper\Getter::getObject($object, $class_name);
				if (empty($object) && intval($this->getRawParameter($parameter_name))) {
					/** @noinspection PhpUnhandledExceptionInspection Useless for developers */
					// This exception will be caught by the main controller : not to be managed by others
					$this->throwException('The object does not exist anymore');
				}
			}
			else {
				// free parameter
				$object = $this->getRawParameter($parameter_name);
			}
			$this->objects[$parameter_name] = $object;
		}
		else {
			// text parameter
			$object = $this->getRawParameter($parameter_name);
			if (
				is_string($object)
				&& (strlen($object) >= 2)
				&& ($object[0] === SL)
				&& ctype_upper($object[1])
				&& !str_contains($object, '?')
			) {
				$object = new Uri($object);
			}
			$this->objects[$parameter_name] = $object;
		}
		if (empty($object)) {
			$built_parameter_name = Builder::className($parameter_name);
			if ($built_parameter_name !== $parameter_name) {
				return $this->getObject(Builder::className($parameter_name));
			}
		}
		return $object;
	}

	//------------------------------------------------------------------------------------ getObjects
	/**
	 * Gets parameters list as objects
	 *
	 * @return array key is the parameter name
	 */
	public function getObjects() : array
	{
		$parameters = [];
		if (!$this->parameters) {
			$this->getMainObject();
		}
		foreach (array_keys($this->parameters) as $parameter_name) {
			$parameters[$parameter_name] = $this->getObject($parameter_name);
		}
		return $parameters;
	}

	//------------------------------------------------------------------------------- getRawParameter
	/**
	 * Gets URI parameter raw value, as it was on original URI
	 *
	 * @param $parameter_name string
	 * @return mixed
	 */
	public function getRawParameter(string $parameter_name) : mixed
	{
		return $this->parameters[$parameter_name] ?? null;
	}

	//------------------------------------------------------------------------------ getRawParameters
	/**
	 * Gets URI parameters raw values, as they were on original URI
	 *
	 * @return array key is the parameter name
	 */
	public function getRawParameters() : array
	{
		return $this->parameters;
	}

	//---------------------------------------------------------------------------- getSelectedObjects
	/**
	 * Read selected objects, no matter method.
	 *
	 * This is a shortcut to Selection::readObjects()
	 *
	 * If it is a checkboxes selection from the list, returns a list of selected elements.
	 * If it is from a unique main object, return this main object.
	 *
	 * If it uses getSelected in controller,
	 * this controller can be compatible with selection in a form and output/edit form buttons.
	 *
	 * @param $form array
	 * @return object[]
	 * @see Selection::readObjects
	 */
	public function getSelectedObjects(array $form) : array
	{
		$selection = new Selection($this, $form);
		return $selection->readObjects();
	}

	//-------------------------------------------------------------------------- getUnnamedParameters
	/**
	 * Gets URI parameters raw values, only those which have no name
	 *
	 * @param $parameters_without_value_too boolean if true, parameter= will be returned as value
	 * @return string[]
	 */
	public function getUnnamedParameters(bool $parameters_without_value_too = false) : array
	{
		if ($parameters_without_value_too) {
			$parameters = [];
			foreach ($this->parameters as $key => $value) {
				if (is_numeric($key)) {
					$parameters[] = $value;
				}
				elseif (!strlen($value)) {
					$parameters[] = $key;
				}
			}
			return $parameters;
		}
		return arrayUnnamedValues($this->parameters);
	}

	//------------------------------------------------------------------------------------------- has
	/**
	 * Returns true if the parameter named $key exist
	 *
	 * @param $key           string
	 * @param $in_values_too boolean if true, strict-search into values too
	 * @return boolean
	 */
	public function has(string $key, bool $in_values_too = false) : bool
	{
		return isset($this->parameters[$key]) || ($in_values_too && $this->hasValue($key, true));
	}

	//-------------------------------------------------------------------------------------- hasValue
	/**
	 * @param $value  mixed
	 * @param $strict boolean
	 * @return boolean
	 */
	public function hasValue(mixed $value, bool $strict = false) : bool
	{
		return in_array($value, $this->parameters, $strict);
	}

	//---------------------------------------------------------------------------------------- isTrue
	/**
	 * @param $key           string
	 * @param $in_values_too boolean
	 * @return boolean
	 */
	public function isTrue(string $key, bool $in_values_too = false) : bool
	{
		return $this->has($key, $in_values_too)
			&& (!isset($this->parameters[$key]) || ($this->parameters[$key]));
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Removes a parameter
	 *
	 * @param $key             integer|string
	 * @param $from_values_too boolean if true, remove from keys and values
	 */
	public function remove(int|string $key, bool $from_values_too = false) : void
	{
		if (isset($this->parameters[$key])) {
			unset($this->parameters[$key]);
		}
		if ($from_values_too && (($key = array_search($key, $this->parameters, true)) !== false)) {
			unset($this->parameters[$key]);
		}
	}

	//----------------------------------------------------------------------------------------- reset
	/**
	 * Resets all parameters and objects
	 */
	public function reset() : void
	{
		$this->objects    = [];
		$this->parameters = array_key_exists('as_widget', $this->parameters)
			? ['as_widget' => $this->parameters['as_widget']] : [];
	}

	//---------------------------------------------------------------------------------------- search
	/**
	 * Returns the key of the parameter having value $string
	 *
	 * @param $value mixed the searched value
	 * @return integer|string|false the name of the found parameter, or false if not found
	 */
	public function search(mixed $value) : int|string|false
	{
		return array_search($value, $this->parameters);
	}

	//------------------------------------------------------------------------------ searchMainObject
	/**
	 * @param $class_name     string
	 * @param $property_names string[]
	 * @return ?object
	 */
	public function searchMainObject(string $class_name, array $property_names) : ?object
	{
		$search = [];
		foreach ($property_names as $property_name) {
			$search[$property_name] = $this->getRawParameter($property_name);
		}
		$object = Dao::searchOne($search, $class_name);
		if ($object) {
			$this->unshift($object);
		}
		return $object;
	}

	//------------------------------------------------------------------------------------------- set
	/**
	 * Sets URI parameter raw value
	 *
	 * @param $parameter_name  string
	 * @param $parameter_value mixed
	 * @return $this
	 */
	public function set(string $parameter_name, mixed $parameter_value) : static
	{
		if (isset($this->objects[$parameter_name])) {
			$this->objects[$parameter_name] = $parameter_value;
		}
		$this->parameters[$parameter_name] = $parameter_value;
		return $this;
	}

	//----------------------------------------------------------------------------------------- shift
	/**
	 * Returns and remove the first parameter value
	 *
	 * @return mixed
	 */
	public function shift() : mixed
	{
		return array_shift($this->parameters);
	}

	//------------------------------------------------------------------------------------ shiftNamed
	/**
	 * Returns and remove the first parameter which key is not an integer and value is not an object
	 *
	 * @return ?string[] first element is the name of the parameter, second element is its value
	 */
	public function shiftNamed() : ?array
	{
		foreach ($this->parameters as $key => $value) {
			if (!is_numeric($key) && !is_object($value)) {
				unset($this->parameters[$key]);
				return [$key, $value];
			}
		}
		return null;
	}

	//----------------------------------------------------------------------------------- shiftObject
	/**
	 * Returns and remove the first parameter which is an object
	 *
	 * @return ?object
	 */
	public function shiftObject() : ?object
	{
		[$key, $value] = $this->firstKeyObject();
		if (isset($key)) {
			unset($this->parameters[$key]);
			return $value;
		}
		return null;
	}

	//---------------------------------------------------------------------------------- shiftUnnamed
	/**
	 * Returns and remove the first unnamed parameter
	 * (which key is an integer and value is not an object)
	 *
	 * @return mixed
	 */
	public function shiftUnnamed() : mixed
	{
		foreach ($this->parameters as $key => $value) {
			if ((is_numeric($key) || ($key === '')) && !is_object($value)) {
				unset($this->parameters[$key]);
				return $value;
			}
		}
		return null;
	}

	//-------------------------------------------------------------------------------- throwException
	/**
	 * @param $message string
	 * @throws Object_Not_Found_Exception
	 */
	protected function throwException(string $message)
	{
		throw new Object_Not_Found_Exception(Loc::tr($message));
	}

	//--------------------------------------------------------------------------------------- toArray
	/**
	 * @param $value array|string
	 * @return array
	 */
	public static function toArray(array|string $value) : array
	{
		if (
			is_string($value)
			&& (strlen($value) > 2)
			&& str_starts_with($value, '[')
			&& str_ends_with($value, ']')
		) {
			$value = explode(',', substr($value, 1, -1));
		}
		elseif (!is_array($value)) {
			$value = [$value];
		}
		return $value;
	}

	//----------------------------------------------------------------------------------------- toGet
	/**
	 * Changes named parameters (which name is not numeric and value not object) into a 'get-like'
	 * argument
	 *
	 * @param $shift boolean if true, get elements will be removed from parameters
	 * @return array
	 */
	public function toGet(bool $shift = false) : array
	{
		$get = [];
		foreach ($this->parameters as $key => $value) {
			if (!is_numeric($key) && !is_object($value)) {
				$get[$key] = $value;
				if ($shift) {
					unset($this->parameters[$key]);
				}
			}
		}
		return $get;
	}

	//--------------------------------------------------------------------------------------- unshift
	/**
	 * Unshift a parameter at beginning of the parameters array
	 *
	 * @param $parameter_value mixed
	 */
	public function unshift(mixed $parameter_value) : void
	{
		if (is_object($parameter_value)) {
			$class_name = get_class($parameter_value);
			if (isset($this->objects[$class_name])) {
				$this->objects[$class_name] = $parameter_value;
			}
			if (isset($this->parameters[$class_name])) {
				unset($this->parameters[$class_name]);
			}
			$this->parameters = arrayMergeRecursive([$class_name => $parameter_value], $this->parameters);
		}
		else {
			$this->unshiftUnnamed($parameter_value);
		}
	}

	//-------------------------------------------------------------------------------- unshiftUnnamed
	/**
	 * Adds an unnamed parameter as first parameter
	 *
	 * @param $parameter_value mixed
	 */
	public function unshiftUnnamed(mixed $parameter_value) : void
	{
		array_unshift($this->parameters, $parameter_value);
	}

}
