<?php
namespace SAF\Framework;

abstract class Html_Template_Funcs
{

	//-------------------------------------------------------------------------------- getApplication
	/**
	 * Returns application name
	 *
	 * @param Html_Template $template
	 * @param object $object
	 * @return string
	 */
	public static function getApplication(Html_Template $template, $object)
	{
		return new Displayable(
			Configuration::current()->getApplicationName(), Displayable::TYPE_CLASS
		);
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * Returns object's class name
	 *
	 * @param Html_Template $template
	 * @param object $object
	 * @return string
	 */
	public static function getClass(Html_Template $template, $object)
	{
		return is_object($object)
			? (
					($object instanceof Set)
					? new Displayable(
						Names::classToSet(Namespaces::shortClassName($object->element_class_name)),
						Displayable::TYPE_CLASS
					)
					: new Displayable(Namespaces::shortClassName(get_class($object)), Displayable::TYPE_CLASS)
				)
			: new Displayable(Namespaces::shortClassName($object), Displayable::TYPE_CLASS);
	}

	//------------------------------------------------------------------------------------ getFeature
	/**
	 * Returns template's feature method name
	 *
	 * @param string $template
	 * @param string $object
	 */
	public static function getFeature($template, $object)
	{
		return new Displayable($template->getFeature(), Displayable::TYPE_METHOD);
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Returns object's properties, and their display and value
	 *
	 * @param Html_Template $template
	 * @param object $object
	 * @return multitype:Reflection_Property
	 */
	public static function getProperties(Html_Template $template, $object)
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
	 * @param Html_Template $template
	 * @param object $object
	 * @return object
	 */
	public static function getTop(Html_Template $template, $object)
	{
		return $template->getObject();
	}

}
