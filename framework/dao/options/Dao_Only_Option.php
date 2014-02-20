<?php
namespace SAF\Framework;

/**
 * A DAO only option
 */
class Dao_Only_Option implements Dao_Option
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
	 * @example Dao::write($user, Dao::only("password"));
	 * Will only write the value of password into the database
	 * @example Dao::write($user, Dao::only(array("login", "password")));
	 * Will write the values of user's login and password into the database
	 *
	 * @param $properties string[]|string
	 * @param $properties,... string[]|string
	 */
	public function __construct($properties = null)
	{
		$this->properties = array();
		foreach (func_get_args() as $properties) {
			if (is_array($properties)) {
				$this->properties += $properties;
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
	 * Returns true if any of the "only" options has the property name
	 *
	 * @param $options  Dao_Option[]
	 * @param $property string
	 * @return boolean
	 */
	public static function have($options, $property)
	{
		foreach ($options as $option) {
			if ($option instanceof Dao_Only_Option) {
				if ($option->has($property)) {
					return true;
				}
			}
		}
		return false;
	}

}
