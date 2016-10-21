<?php
namespace SAF\Framework\Dao\Option;

use SAF\Framework\Dao\Option;

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
	public $properties;

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
	 *
	 * @param $properties string[]|string ...
	 */
	public function __construct($properties)
	{
		$this->properties = [];
		$this->add(func_get_args());
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $properties string[]|string
	 */
	public function add($properties)
	{
		foreach (func_get_args() as $properties) {
			if (is_array($properties)) {
				foreach ($properties as $property) {
					$this->add($property);
				}
			}
			elseif (is_string($properties)) {
				$this->properties[] = $properties;
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
	public function has($property)
	{
		return in_array($property, $this->properties);
	}

	//------------------------------------------------------------------------------------------ have
	/**
	 * Returns true if any of the 'properties' options has the property name
	 *
	 * If $no_property_returns_true is set to false, the function will return false if there is none
	 * If $no_property_returns_true is kept to true, the function will return true if there is none
	 *
	 * @param $options              Option[]
	 * @param $property             string
	 * @param $no_property_returns_true boolean if there is no Properties option, returns this value
	 * @return boolean
	 */
	public static function have($options, $property, $no_property_returns_true = true)
	{
		$default = $no_property_returns_true;
		foreach ($options as $option) {
			if ($option instanceof static) {
				$default = false;
				if ($option->has($property)) {
					return true;
				}
			}
		}
		return $default;
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove a property
	 *
	 * @param $property string
	 * @return integer How many properties were removed. 0 if was not here.
	 */
	public function remove($property)
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
	public static function removeAll($options, $property)
	{
		$removed = 0;
		foreach ($options as $option) {
			if ($option instanceof static) {
				$removed += $option->remove($property);
			}
		}
		return $removed;
	}

}
