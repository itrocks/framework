<?php
namespace SAF\Framework;

require_once "framework/core/mappers/Getter.php";
require_once "framework/core/toolbox/Namespaces.php";

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

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Get URI parameter as an object
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
		elseif (is_numeric($this->parameters[$parameter_name])) {
			$class_name = Namespaces::fullClassName($parameter_name);
			if (class_exists($class_name)) {
				// object parameter
				$object = Getter::getObject($this->parameters[$parameter_name] + 0, $class_name);
				$this->objects[$parameter_name] = $object;
			}
			else {
				// free parameter
				$object = $this->parameters[$parameter_name];
				$this->objects[$parameter_name] = $object;
			}
		}
		else {
			// text parameter
			$object = $this->parameters[$parameter_name];
			$this->objects[$parameter_name] = $object;
		}
		return $object;
	}

	//------------------------------------------------------------------------------------ getObjects
	/**
	 * Get parameters list as objects
	 *
	 * @return object[] indiced by parameter name
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
	 * Get URI parameter raw value, as it was on original URI
	 *
	 * @param $parameter_name string
	 * @return mixed
	 */
	public function getRawParameter($parameter_name)
	{
		return $this->parameters[$parameter_name];
	}

	//--------------------------------------------------------------------------------- getParameters
	/**
	 * Get URI parameters raw values, as they were on original URI
	 *
	 * @return mixed[] indice is the parameter name
	 */
	public function getRawParameters()
	{
		return $this->parameters;
	}

	//-------------------------------------------------------------------------- getUnnamedParameters
	/**
	 * Get URI parameters raw values, only those which have no name
	 */
	public function getUnnamedParameters()
	{
		return arrayUnnamedValues($this->parameters);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove a parameter
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
	 * Set URI parameter raw value
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

	//----------------------------------------------------------------------------------------- shift
	/**
	 * Returns and remove the first unnamed parameter (which key is an integer and value is not an object)
	 *
	 * @return mixed|null
	 */
	public function shift()
	{
		foreach ($this->parameters as $key => $value) {
			if (is_numeric($key) && !is_object($value)) {
				unset($this->parameters[$key]);
				return $value;
			}
		}
		return null;
	}

	//--------------------------------------------------------------------------------------- unshift
	/**
	 * Adds an unnamed parameter as first parameter
	 *
	 * @param $parameter_value mixed
	 */
	public function unshift($parameter_value)
	{
		array_unshift($this->parameters, $parameter_value);
	}

}
