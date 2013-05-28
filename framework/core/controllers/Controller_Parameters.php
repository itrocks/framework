<?php
namespace SAF\Framework;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/mappers/Getter.php";
require_once "framework/core/toolbox/Namespaces.php";

/**
 * Controller parameters contains what objects are passed into the controller's URI
 */
class Controller_Parameters
{

	//-------------------------------------------------------------------------------------- $objects
	/**
	 * @var object[] indices are parameters names (ie object class short name)
	 */
	private $objects = array();

	//----------------------------------------------------------------------------------- $parameters
	/**
	 * @var integer[] indices are parameters names (ie object class short name)
	 */
	private $parameters = array();

	//-------------------------------------------------------------------------------------- addValue
	/**
	 * Adds a parameter without name value
	 *
	 * @param $parameter_value mixed
	 * @return Controller_Parameters
	 */
	public function addValue($parameter_value)
	{
		$this->parameters[] = $parameter_value;
		return $this;
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * Gets parameters count
	 *
	 * @return integer
	 */
	public function count()
	{
		return count($this->parameters);
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Gets URI parameter as an object
	 *
	 * Object is of class $parameter name, and is read from current data link using the parameter
	 * value as identifier.
	 *
	 * @param $parameter_name string
	 * @return object
	 */
	public function getObject($parameter_name)
	{
		if (isset($this->objects[$parameter_name])) {
			// parameter is in cache
			$object = $this->objects[$parameter_name];
		}
		elseif (is_numeric($this->getRawParameter($parameter_name))) {
			$class_name = Namespaces::fullClassName($parameter_name);
			if (class_exists($class_name)) {
				// object parameter
				$object = Getter::getObject($this->getRawParameter($parameter_name) + 0, $class_name);
				$this->objects[$parameter_name] = $object;
			}
			else {
				// free parameter
				$object = $this->getRawParameter($parameter_name);
				$this->objects[$parameter_name] = $object;
			}
		}
		else {
			// text parameter
			$object = $this->getRawParameter($parameter_name);
			$this->objects[$parameter_name] = $object;
		}
		return $object;
	}

	//------------------------------------------------------------------------------------ getObjects
	/**
	 * Gets parameters list as objects
	 *
	 * @return mixed[] indiced by parameter name
	 */
	public function getObjects()
	{
		$parameters = array();
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
	public function getRawParameter($parameter_name)
	{
		if(isset($this->parameters[$parameter_name]))
			return $this->parameters[$parameter_name];
		else
			return null;
	}

	//--------------------------------------------------------------------------------- getParameters
	/**
	 * Gets URI parameters raw values, as they were on original URI
	 *
	 * @return mixed[] indice is the parameter name
	 */
	public function getRawParameters()
	{
		return $this->parameters;
	}

	//-------------------------------------------------------------------------- getUnnamedParameters
	/**
	 * Gets URI parameters raw values, only those which have no name
	 */
	public function getUnnamedParameters()
	{
		return arrayUnnamedValues($this->parameters);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Removes a parameter
	 *
	 * @param $key integer|string
	 */
	public function remove($key)
	{
		if (isset($this->parameters[$key])) {
			unset($this->parameters[$key]);
		}
	}

	//------------------------------------------------------------------------------------------- set
	/**
	 * Sets URI parameter raw value
	 *
	 * @param $parameter_name  string
	 * @param $parameter_value mixed
	 * @return Controller_Parameters
	 */
	public function set($parameter_name, $parameter_value)
	{
		$this->parameters[$parameter_name] = $parameter_value;
		return $this;
	}

	//---------------------------------------------------------------------------------- shiftUnnamed
	/**
	 * Returns and remove the first unnamed parameter (which key is an integer and value is not an object)
	 *
	 * @return mixed|null
	 */
	public function shiftUnnamed()
	{
		foreach ($this->parameters as $key => $value) {
			if (is_numeric($key) && !is_object($value)) {
				unset($this->parameters[$key]);
				return $value;
			}
		}
		return null;
	}

	//-------------------------------------------------------------------------------- unshiftUnnamed
	/**
	 * Adds an unnamed parameter as first parameter
	 *
	 * @param $parameter_value mixed
	 */
	public function unshiftUnnamed($parameter_value)
	{
		array_unshift($this->parameters, $parameter_value);
	}

	//--------------------------------------------------------------------------------------- unshift
	/**
	 * Unshifts a parameter at beginning of the parameters array
	 *
	 * @param $parameter_value mixed
	 */
	public function unshift($parameter_value)
	{
		if (is_object($parameter_value)) {
			$this->parameters = arrayMergeRecursive(
				array(get_class($parameter_value) => $parameter_value), $this->parameters
			);
		}
		else {
			$this->unshiftUnnamed($parameter_value);
		}
	}

}
