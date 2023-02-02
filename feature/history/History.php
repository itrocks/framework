<?php
namespace ITRocks\Framework\Feature;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Attribute\Class_\Set;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
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
#[Store]
abstract class History
{

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @default Date_Time::now
	 */
	public Date_Time|string $date;

	//------------------------------------------------------------------------------------ $new_value
	public string $new_value;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * You must @override object @var Class_Name into the final class
	 * Or create another property with @replaces object
	 *
	 * @var object
	 */
	public object $object;

	//------------------------------------------------------------------------------------ $old_value
	public string $old_value;

	//-------------------------------------------------------------------------------- $property_name
	public string $property_name;

	//----------------------------------------------------------------------------------------- $user
	/**
	 * @default User::current
	 */
	public User $user;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(
		object $object = null, string $property_name = null, mixed $old_value = null,
		mixed $new_value = null
	) {
		if (!$object || !$property_name) {
			return;
		}
		$this->object        = $object;
		$this->property_name = $property_name;
		$this->old_value     = (is_object($old_value) && Dao::getObjectIdentifier($old_value))
			? Dao::getObjectIdentifier($old_value)
			: strval($old_value);
		$this->new_value     = (is_object($new_value) && Dao::getObjectIdentifier($new_value))
			? Dao::getObjectIdentifier($new_value)
			: strval($new_value);
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return Loc::dateToLocale($this->date);
	}

	//----------------------------------------------------------------------------------- hasNewValue
	public function hasNewValue() : bool
	{
		return $this->new_value !== '';
	}

	//----------------------------------------------------------------------------------- hasOldValue
	public function hasOldValue() : bool
	{
		return $this->old_value !== '';
	}

	//-------------------------------------------------------------------------------------- newClass
	public function newClass() : string
	{
		return $this->hasOldValue() ? 'change' : 'add';
	}

	//-------------------------------------------------------------------------------------- oldClass
	public function oldClass() : string
	{
		return $this->hasNewValue() ? 'change' : 'remove';
	}

}
