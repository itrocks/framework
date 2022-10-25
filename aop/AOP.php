<?php
namespace ITRocks\Framework;

/**
 * Tools to manage AOP on your objects
 */
abstract class AOP
{

	//--------------------------------------------------------------------------------- propertiesOff
	/**
	 * Deactivate AOP on properties for one given object
	 *
	 * If the object was already deactivated, it does not matter : this does nothing
	 *
	 * @param $object object
	 */
	public static function propertiesOff(object $object)
	{
		if (isset($object->_)) {
			$object->_AOP_off = $object->_;
			unset($object->_);
		}
	}

	//---------------------------------------------------------------------------------- propertiesOn
	/**
	 * Re-activate AOP after a call to propertiesOff for this object
	 *
	 * If the object was not deactivated, it does not matter : this does nothing
	 *
	 * @param $object object
	 */
	public static function propertiesOn(object $object)
	{
		if (isset($object->_AOP_off)) {
			$object->_ = $object->_AOP_off;
			unset($object->_AOP_off);
		}
	}

}
