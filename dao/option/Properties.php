<?php
namespace ITRocks\Framework\Dao\Option;

use ITRocks\Framework\Dao\Option;

/**
 * Base option class for collections of properties (eg Exclude, Only)
 */
abstract class Properties implements Option
{

	//----------------------------------------------------------------------------------- $properties
	/**
	 * Properties path used to limit a Dao operation range
	 *
	 * @mandatory
	 * @var string[]
	 */
	public array $properties;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a DAO Properties option
	 *
	 * @example Dao::write($user, Dao::only('password'));
	 * Will only write the value of password into the database
	 * @example Dao::write($user, Dao::only('login', 'password'));
	 * Will write the values of user's login and password into the database
	 * @example Dao::write($user, Dao::only(['login', 'password']));
	 * Will write the values of user's login and password into the database
	 * @param $properties string|string[] ...
	 */
	public function __construct(array|string... $properties)
	{
		$this->properties = [];
		$this->add(func_get_args());
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $properties string|string[] ... Each property can be a property.path
	 */
	public function add(array|string... $properties) : void
	{
		foreach (func_get_args() as $properties) {
			if (is_array($properties)) {
				foreach ($properties as $property) {
					$this->add($property);
				}
			}
			elseif (is_string($properties)) {
				if (str_contains($properties, DOT)) {
					$property = '';
					foreach (explode(DOT, $properties) as $property_element) {
						$property .= ($property ? DOT : '') . $property_element;
						$this->properties[] = $property;
					}
				}
				else {
					$this->properties[] = $properties;
				}
			}
		}
	}

	//------------------------------------------------------------------------------------------- has
	/**
	 * Returns true if the option has the property name
	 *
	 * @param $property string
	 * @return boolean
	 */
	public function has(string $property) : bool
	{
		return in_array($property, $this->properties, true);
	}

	//------------------------------------------------------------------------------------------ have
	/**
	 * Returns true if any of the 'properties' options has the property name
	 *
	 * If $no_property_returns_true is set to false, the function will return false if there is none
	 * If $no_property_returns_true is kept to true, the function will return true if there is none
	 *
	 * @param $options                Option[]
	 * @param $property_name          string
	 * @param $no_option_returns_true boolean if there is no Properties option, returns this value
	 * @return boolean
	 */
	public static function have(
		array $options, string $property_name, bool $no_option_returns_true = true
	) : bool
	{
		$default = $no_option_returns_true;
		foreach ($options as $option) {
			if ($option instanceof static) {
				if ($option->has($property_name)) {
					return true;
				}
				$default = false;
			}
		}
		return $default;
	}

	//------------------------------------------------------------------------------------ instanceIn
	/**
	 * Returns true options contains at least one option of static class
	 *
	 * @deprecated should use Has_In and its method in() instead
	 * @param $options Option[]
	 * @return ?object if found, the first instance of the class into $options
	 * @see Has_In::in
	 */
	public static function instanceIn(array $options) : ?object
	{
		return instanceIn(static::class, $options);
	}

	//------------------------------------------------------------------------------------ properties
	/**
	 * Gets all properties from a list of Option that can contain one or more Only
	 *
	 * @param $options Option[]
	 * @return string[]
	 */
	public static function properties(array $options) : array
	{
		$properties = [];
		foreach ($options as $option) {
			if ($option instanceof static) {
				$properties = array_merge($properties, $option->properties);
			}
		}
		return $properties;
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove a property
	 *
	 * @param $property string
	 * @return integer How many properties were removed. 0 if was not here.
	 */
	public function remove(string $property) : int
	{
		$removed = 0;
		while (($key = array_search($property, $this->properties)) !== false) {
			unset($this->properties[$key]);
			$removed ++;
		}
		return $removed;
	}

	//------------------------------------------------------------------------------------- removeAll
	/**
	 * Remove all references to a property
	 *
	 * @param $options  Option[]
	 * @param $property string
	 * @return integer the count of property removed from options
	 */
	public static function removeAll(array $options, string $property) : int
	{
		$removed = 0;
		foreach ($options as $option) {
			if ($option instanceof static) {
				$removed += $option->remove($property);
			}
		}
		return $removed;
	}

	//------------------------------------------------------------------------------- subObjectOption
	/**
	 * @param $property_path        string
	 * @param $always_return_option boolean If true : return an empty option instead of null
	 * @return ?static null if there is no path for $property_path into
	 */
	public function subObjectOption(
		string $property_path, bool $always_return_option = false
	) : ?static
	{
		$property_path .= DOT;
		$length         = strlen($property_path);
		foreach ($this->properties as $property) {
			if (substr($property, 0, $length) === $property_path) {
				$properties[] = substr($property, $length);
			}
		}
		if (isset($properties)) {
			$option             = clone $this;
			$option->properties = $properties;
			return $option;
		}
		if ($always_return_option) {
			$class = static::class;
			return new $class;
		}
		return null;
	}

}
