<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Every _History class should extend this
 *
 * You must @override object @var Class_Name into the final class
 * Or create another property with @replaces object
 *
 * @representative object, date, property_name, old_value, new_value
 * @set            History
 * @sort           date, user
 */
abstract class History
{

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @default Date_Time::now
	 * @link    DateTime
	 * @var     Date_Time
	 */
	public $date;

	//------------------------------------------------------------------------------------ $new_value
	/**
	 * @var string|mixed
	 */
	public $new_value;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * You must @override object @var Class_Name into the final class
	 * Or create another property with @replaces object
	 *
	 * @link      Object
	 * @mandatory
	 * @var       object
	 */
	public $object;

	//------------------------------------------------------------------------------------ $old_value
	/**
	 * @var string|mixed
	 */
	public $old_value;

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * @var string
	 */
	public $property_name;

	//----------------------------------------------------------------------------------------- $user
	/**
	 * @default User::current
	 * @link    Object
	 * @var     User
	 */
	public $user;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $object        object
	 * @param $property_name string
	 * @param $old_value     mixed
	 * @param $new_value     mixed
	 */
	public function __construct(
		$object = null, $property_name = null, $old_value = null, $new_value = null
	) {
		if (isset($object) && isset($property_name)) {
			$this->object        = $object;
			$this->property_name = $property_name;
			$this->old_value     = (is_object($old_value) && Dao::getObjectIdentifier($old_value))
				? Dao::getObjectIdentifier($old_value)
				: strval($old_value);
			$this->new_value     = (is_object($new_value) && Dao::getObjectIdentifier($new_value))
				? Dao::getObjectIdentifier($new_value)
				: strval($new_value);
		}
		if (is_null($this->date)) {
			$this->date = Date_Time::now();
		}
		if (is_null($this->user)) {
			$this->user = User::current();
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return empty($this->date) ? '' : Loc::dateToLocale($this->date);
	}

	//----------------------------------------------------------------------------------- hasNewValue
	/**
	 * @return boolean
	 */
	public function hasNewValue()
	{
		return strlen($this->new_value);
	}

	//----------------------------------------------------------------------------------- hasOldValue
	/**
	 * @return boolean
	 */
	public function hasOldValue()
	{
		return strlen($this->old_value);
	}

	//-------------------------------------------------------------------------------------- newClass
	/**
	 * @return string
	 */
	public function newClass()
	{
		return $this->hasOldValue() ? 'change' : 'add';
	}

	//-------------------------------------------------------------------------------------- oldClass
	/**
	 * @return string
	 */
	public function oldClass()
	{
		return $this->hasNewValue() ? 'change' : 'remove';
	}

}
