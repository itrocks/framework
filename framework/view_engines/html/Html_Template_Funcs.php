<?php
namespace Framework;

class Html_Template_Funcs
{

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * Returns object's class name
	 *
	 * @param  Html_Template $template
	 * @param  object $object
	 * @return string
	 */
	public static function getClass($template, $object)
	{
		return is_object($object) ? get_class($object) : "";
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Returns object's properties, and their display and value
	 *
	 * @param  Html_Template $template
	 * @param  object $object
	 * @return multitype:Reflection_Property
	 */
	public static function getProperties($template, $object)
	{
		$fields = Class_Fields::accessFields(get_class($object));
		foreach ($fields as $field_name => $field) {
			$fields[$field_name]->display = Names::propertyToDisplay($field_name);
			$fields[$field_name]->value = $object->$field_name;
		}
		Class_Fields::accessFieldsDone(get_class($object));
		return $fields;
	}

	//---------------------------------------------------------------------------------------- getTop
	/**
	 * Returns template's top object
	 * (use it inside of loops)
	 *
	 * @param  Html_Template $template
	 * @param  object $object
	 * @return object
	 */
	public static function getTop($template, $object)
	{
		return $template->object;
	}

}
