<?php
namespace ITRocks\Framework\Feature;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Attribute\Class_\Set;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\User;

/**
 * Every _History class should extend this
 *
 * You must @override object @var Class_Name into the final class
 * Or create another property with @replaces object
 *
 * @representative object, date, property_name, old_value, new_value
 * @sort           date, user
 */
#[Set('History')]
abstract class History
{

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @default Date_Time::now
	 * @link    DateTime
	 * @var     Date_Time|string
	 */
	public Date_Time|string $date;

	//------------------------------------------------------------------------------------ $new_value
	/**
	 * @var string
	 */
	public string $new_value;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * You must @override object @var Class_Name into the final class
	 * Or create another property with @replaces object
	 *
	 * @link      Object
	 * @mandatory
	 * @var       object
	 */
	public object $object;

	//------------------------------------------------------------------------------------ $old_value
	/**
	 * @var string
	 */
	public string $old_value;

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * @var string
	 */
	public string $property_name;

	//----------------------------------------------------------------------------------------- $user
	/**
	 * @default User::current
	 * @link    Object
	 * @var     User
	 */
	public User $user;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $object        object|null
	 * @param $property_name string|null
	 * @param $old_value     mixed
	 * @param $new_value     mixed
	 */
	public function __construct(
		object $object = null, string $property_name = null, mixed $old_value = null,
		mixed $new_value = null
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
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return Loc::dateToLocale($this->date);
	}

	//----------------------------------------------------------------------------------- hasNewValue
	/**
	 * @return boolean
	 */
	public function hasNewValue() : bool
	{
		return $this->new_value !== '';
	}

	//----------------------------------------------------------------------------------- hasOldValue
	/**
	 * @return boolean
	 */
	public function hasOldValue() : bool
	{
		return $this->old_value !== '';
	}

	//-------------------------------------------------------------------------------------- newClass
	/**
	 * @return string
	 */
	public function newClass() : string
	{
		return $this->hasOldValue() ? 'change' : 'add';
	}

	//-------------------------------------------------------------------------------------- oldClass
	/**
	 * @return string
	 */
	public function oldClass() : string
	{
		return $this->hasNewValue() ? 'change' : 'remove';
	}

}
