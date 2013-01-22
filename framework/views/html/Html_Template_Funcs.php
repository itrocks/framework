<?php
namespace SAF\Framework;

abstract class Html_Template_Funcs
{

	//-------------------------------------------------------------------------------------- getCount
	/**
	 * Returns array count
	 *
	 * @param Html_Template $template
	 * @param multitype:mixed $object
	 */
	public static function getCount(Html_Template $template, $objects)
	{
		return count($objects);
	}

	//-------------------------------------------------------------------------------- getApplication
	/**
	 * Returns application name
	 *
	 * @param Html_Template $template
	 * @param multitype:mixed $objects
	 * @return string
	 */
	public static function getApplication(Html_Template $template, $objects)
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
	 * @param multitype:mixed $objects
	 * @return string
	 */
	public static function getClass(Html_Template $template, $objects)
	{
		$object = reset($objects);
		return is_object($object)
			? (
					($object instanceof Set)
					? new Displayable(Names::classToSet($object->element_class_name), Displayable::TYPE_CLASS)
					: new Displayable(Namespaces::shortClassName(get_class($object)), Displayable::TYPE_CLASS)
				)
			: new Displayable(Namespaces::shortClassName($object), Displayable::TYPE_CLASS);
	}

	//------------------------------------------------------------------------------------ getDisplay
	/**
	 * Return object's display
	 *
	 * @param Html_Template $template
	 * @param multitype:mixed $objects
	 * @return string
	 */
	public static function getDisplay(Html_Template $template, $objects)
	{
		$object = reset($objects);
		if ($object instanceof Reflection_Property) {
			return Names::propertyToDisplay($object->name);
		}
		elseif ($object instanceof Reflection_Class) {
			return Names::classToDisplay($object->name);
		}
		elseif ($object instanceof Reflection_Method) {
			return Names::methodToDisplay($object->name);
		}
		elseif (is_object($object)) {
			return (new Displayable(get_class($object)))->display();
		}
		else {
			return $object;
		}
	}

	//------------------------------------------------------------------------------------ getFeature
	/**
	 * Returns template's feature method name
	 *
	 * @param string $template
	 * @param multitype:mixed $objects
	 */
	public static function getFeature(Html_Template $template, $objects)
	{
		return new Displayable($template->getFeature(), Displayable::TYPE_METHOD);
	}

	//------------------------------------------------------------------------------------- getFormat
	/**
	 * Return formatted value
	 *
	 * @param Html_Template $template
	 * @param multitype:mixed $object
	 */
	public static function getFormat(Html_Template $template, $objects)
	{
		$object = self::getObject($template, $objects);
echo "! format " . get_class($object) . " = " . var_dump(reset($objects)) . "<br>";
		return reset($objects);
	}

	//---------------------------------------------------------------------------------------- getHas
	/**
	 * Returns true if the element is not empty
	 * (usefull for conditions on arrays)
	 *
	 * @param Html_Template $template
	 * @param multitype:mixed $objects
	 * @return boolean
	 */
	public static function getHas(Html_Template $template, $objects)
	{
		$object = reset($objects);
		return !empty($object);
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Returns nearest object from templating tree
	 *
	 * @param Html_Template $template
	 * @param multitype:mixed $objects
	 * @return object
	 */
	public static function getObject(Html_Template $template, $objects)
	{
		$object = reset($objects);
		while (isset($object) && !is_object($object)) {
			$object = next($objects);
		}
		return $object;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Returns object's properties, and their display and value
	 *
	 * @param Html_Template $template
	 * @param multitype:mixed $objects
	 * @return multitype:Reflection_Property_Value
	 */
	public static function getProperties(Html_Template $template, $objects)
	{
		$object = reset($objects);
		$properties_filter = $template->getParameter("properties_filter");
		$class = Reflection_Class::getInstanceOf($object);
		$properties = $class->accessProperties();
		$result_properties = array();
		foreach ($properties as $property_name => $property) {
			if (isset($properties_filter) && !in_array($property_name, $properties_filter)) {
				unset($properties[$property_name]);
			}
			else {
				$result_properties[$property_name] = new Reflection_Property_Value($object, $property);
			}
		}
		$class->accessPropertiesDone();
		return $result_properties;
	}

	//------------------------------------------------------------------------ getPropertiesOutOfTabs
	/**
	 * Returns object's properties, and their display and value, but only if they are not already into a tab
	 *
	 * @param Html_Template $template
	 * @param multitype:mixed $objects
	 * @return multitype:Reflection_Property_Value
	 */
	public static function getPropertiesOutOfTabs(Html_Template $template, $objects)
	{
		$properties = array();
		foreach (self::getProperties($template, $objects) as $property_name => $property) {
			if (!isset($property->tab_path)) {
				$properties[$property_name] = $property;
			}
		}
		return $properties;
	}

	//---------------------------------------------------------------------------------------- getTop
	/**
	 * Returns template's top object
	 * (use it inside of loops)
	 *
	 * @param Html_Template $template
	 * @param multitype:mixed $objects
	 * @return object
	 */
	public static function getTop(Html_Template $template, $objects)
	{
		return $template->getObject();
	}

}
