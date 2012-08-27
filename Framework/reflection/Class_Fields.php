<?php

class Class_Fields
{

	private static $accessible_fields_count = array();

	private static $accessible_fields_map = array();

	private static $private_fields_map = array();

	//--------------------------------------------------------------------------------- _accessFields
	private static function _accessFields($object_class)
	{
		$fields = Class_Fields::$accessible_fields_map[$object_class];
		$parent_class = get_parent_class($object_class);
		if ($parent_class) {
			$parent_fields = Class_Fields::_accessFields($parent_class);
		}
		if ($fields !== null) {
			Class_Fields::$accessible_fields_count[$object_class] ++;
		} else {
			$fields = $parent_fields;
			$private_fields = array();
			$reflection_class = new Reflection_Class($object_class);
			foreach ($reflection_class->getProperties() as $field) {
				$is_accessible = $field->isPublic();
				if (!$is_accessible) {
					$field->setAccessible(true);
					$private_fields[] = $field;
				}
				$fields[$field->getName()] = $field;
			}
			Class_Fields::$accessible_fields_count[$object_class] = 0;
			Class_Fields::$accessible_fields_map[$object_class] = $fields;
			Class_Fields::$private_fields_map[$object_class] = $private_fields;
		}
		return $fields;
	}

	//----------------------------------------------------------------------------- _accessFieldsDone
	private static function _accessFieldsDone($object_class)
	{
		$count = Class_Fields::$accessible_fields_count[$object_class];
		if ($count > 0) {
			Class_Fields::$accessible_fields_count[$object_class] --;
		} else {
			unset(Class_Fields::$accessible_fields_count[$object_class]);
			unset(Class_Fields::$accessible_fields_map[$object_class]);
			while ($field = array_shift(Class_Fields::$private_fields_map[$object_class])) {
				$field->setAccessible(false);
			}
		}
		$parent_class = get_parent_class($object_class);
		if ($parent_class) {
			Class_Fields::_accessFieldsDone($parent_class);
		}
	}

	//--------------------------------------------------------------------------------------- _fields
	private static function _fields($object_class, $fields)
	{
		$parent_class = get_parent_class($object_class);
		if ($parent_class) {
			$fields = Class_Fields::_fields($parent_class, $fields);
		}
		$reflection_class = new Reflection_Class($object_class);
		foreach ($reflection_class->getProperties() as $field) {
			$fields[$field->getName()] = $field;
		}
		return $fields;
	}

	//---------------------------------------------------------------------------------- accessFields
	/**
	 * @param  string $object_class
	 * @return Reflection_Property[]
	 */
	public static function accessFields($object_class)
	{
		return Class_Fields::_accessFields(Instantiator::getClass($object_class));
	}

	//------------------------------------------------------------------------------ accessFieldsDone
	/**
	 * @param string $object_class
	 */
	public static function accessFieldsDone($object_class)
	{
		return Class_Fields::_accessFieldsDone(Instantiator::getClass($object_class));
	}

	//---------------------------------------------------------------------------------------- fields
	/**
	 * @param  string   $object_class
	 * @param  string[] $fields
	 * @return Reflection_Property[]
	 */
	public static function fields($object_class, $fields = array())
	{
		return Class_Fields::_fields(Instantiator::getClass($object_class), $fields);
	}

}
