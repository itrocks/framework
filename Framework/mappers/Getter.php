<?php

class Getter
{

	//------------------------------------------------------------------------- getGetterForFieldName
	/**
	 * @param  string $field_name
	 * @return string
	 */
	private static function getDefaultGetterForFieldName($field_name)
	{
		$getter = "get";
		$split = split("_", $field_name);
		foreach ($split as $spit) {
			if (strlen($spit)) {
				$getter .= ucfirst($spit);
			}
		}
		return $getter;
	}

	//------------------------------------------------------------------------------------- getGetter
	/**
	 * @param  mixed $field Reflection_Property, string
	 * @return mixed Reflection_Method, string
	 */
	public static function getGetter($field)
	{
		if ($field instanceof Reflection_Property) {
			return Getter::getGetterForField($field);
		} else {
			return Getter::getDefaultGetterForFieldName($field);
		}
	}

	//----------------------------------------------------------------------------- getGetterForField
	/**
	 * @param  Reflection_Property $field
	 * @return Reflection_Method
	 */
	private static function getGetterForField($field)
	{
		$method_name = "";
		$annotation = $field->getAnnotation("getter");
		if ($annotation) {
			$method_name = $annotation->value;
		} else {
			$method_name = Getter::getDefaultGetterForFieldName($field->getName());
		}
		$getter = null;
		$object_class = $field->getDeclaringClass();
		while ($object_class && !$getter) {
			$getter = $object_class->getMethod($method_name);
			if (!$getter) {
				$object_class = $object_class->getParentClass();
			}
		}
		return $getter;
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * @param  mixed  $object
	 * @param  string $object_class
	 * @return Object
	 */
	public static function getObject($object, $object_class)
	{
		if (is_int($object)) {
			$object = Connected_Environment::getCurrent()->getDataLink()->read($object, $object_class);
		}
		return $object;
	}

}
