<?php
namespace SAF\Framework\Dao\Option;

use SAF\Framework\Dao\Option;

/**
 * A DAO only option
 */
class Only implements Option
{

	//---------------------------------------------------------------------------------------- $count
	/**
	 * Properties path used to limit a Dao operation range
	 *
	 * @mandatory
	 * @var string[]
	 */
	public $properties;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a DAO only option
	 *
	 * @example Dao::write($user, Dao::only('password'));
	 * Will only write the value of password into the database
	 * @example Dao::write($user, Dao::only(['login', 'password')));
	 * Will write the values of user's login and password into the database
	 *
	 * @param $properties string[]|string
	 * @param $properties,... string[]|string
	 */
	public function __construct($properties)
	{
		$this->properties = [];
		foreach (func_get_args() as $properties) {
			if (is_array($properties)) {
				$this->properties = array_merge($this->properties, $properties);
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
	 * @param $property
	 * @return boolean
	 */
	public function has($property)
	{
		return in_array($property, $this->properties);
	}

	//------------------------------------------------------------------------------------------ have
	/**
	 * Returns true if any of the 'only' options has the property name
	 *
	 * @param $options  Option[]
	 * @param $property string
	 * @return boolean
	 */
	public static function have($options, $property)
	{
		foreach ($options as $option) {
			if ($option instanceof Only) {
				if ($option->has($property)) {
					return true;
				}
			}
		}
		return false;
	}

}
