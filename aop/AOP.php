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
	 * If the object was already deactivated, it does not matter : this does nothing
	 */
	public static function propertiesOff(object $object) : void
	{
		if (!isset($object->_)) return;
		$object->_AOP_off = $object->_;
		unset($object->_);
	}

	//---------------------------------------------------------------------------------- propertiesOn
	/**
	 * Re-activate AOP after a call to propertiesOff for this object
	 * If the object was not deactivated, it does not matter : this does nothing
	 */
	public static function propertiesOn(object $object) : void
	{
		if (isset($object->_AOP_off)) return;
		$object->_ = $object->_AOP_off;
		unset($object->_AOP_off);
	}

}
