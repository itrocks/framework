<?php
namespace SAF\Framework;

abstract class Html_Template_Funcs
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
		if (is_object($object)) {
			return Namespaces::shortClassName(get_class($object));
		}
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
		$class = Reflection_Class::getInstanceOf($object);
		$properties = $class->accessProperties();
		foreach ($properties as $property_name => $property) {
			$property->display = Names::propertyToDisplay($property_name);
			$property->value = $object->$property_name;
		}
		$class->accessPropertiesDone();
		return $properties;
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
