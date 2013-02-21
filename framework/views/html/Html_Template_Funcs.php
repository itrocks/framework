<?php
namespace SAF\Framework;

/**
 * @todo $objects will become a public property of Html_Template, then remove $objects arguments
 */
abstract class Html_Template_Funcs
{

	//-------------------------------------------------------------------------------- getApplication
	/**
	 * Returns application name
	 *
	 * @param $template Html_Template
	 * @param $objects mixed[]
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
	 * @param $template Html_Template
	 * @param $objects mixed[]
	 * @return string
	 */
	public static function getClass(Html_Template $template, $objects)
	{
		$object = reset($objects);
		return is_object($object)
			? (
					($object instanceof Set)
					? new Displayable(Names::classToSet($object->element_class_name), Displayable::TYPE_CLASS)
					: new Displayable(get_class($object), Displayable::TYPE_CLASS)
				)
			: new Displayable($object, Displayable::TYPE_CLASS);
	}

	//-------------------------------------------------------------------------------------- getCount
	/**
	 * Returns array count
	 *
	 * @param $template Html_Template
	 * @param $objects  mixed[]
	 * @return integer
	 */
	public static function getCount(Html_Template $template, $objects)
	{
		return count($objects);
	}

	//------------------------------------------------------------------------------------ getDisplay
	/**
	 * Return object's display
	 *
	 * @param $template Html_Template
	 * @param $objects mixed[]
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
			return (new Displayable(get_class($object), Displayable::TYPE_CLASS))->display();
		}
		else {
			return $object;
		}
	}

	//--------------------------------------------------------------------------------------- getEdit
	/**
	 * Return an HTML edit widget for current property or List_Data property
	 *
	 * @param $template Html_Template
	 * @param $objects  mixed[]
	 * @param $prefix object
	 * @return string
	 */
	public static function getEdit(Html_Template $template, $objects, $prefix = null)
	{
		if (count($objects) > 2) {
			// from a List_Data
			$property = reset($objects);
			next($objects);
			$list_data = next($objects);
			if ($list_data instanceof Default_List_Data) {
				$class_name = $list_data->element_class_name;
				list($property, $property_path, $value) = self::toEditPropertyExtra($class_name, $property);
				$property_edit = new Html_Builder_Property_Edit($property, $value, $prefix);
				$property_edit->name = $property_path;
				return $property_edit->build();
			}
		}
		else {
			// from any sub-part of ...
			$property = self::getObject($template, $objects);
			if ($property instanceof Reflection_Property_Value) {
				// ... a Reflection_Property_Value
				return (new Html_Builder_Property_Edit($property, $property->value()))->build();
			}
			elseif ($property instanceof Reflection_Property) {
				// ... a Reflection_Property
				return (new Html_Builder_Property_Edit($property))->build();
			}
			elseif (is_object($property)) {
				// ... an object and it's property name
				$property_name = prev($objects);
				$property = Reflection_Property::getInstanceOf($property, $property_name);
				if ($property != null) {
					return (new Html_Builder_Property_Edit($property))->build();
				}
			}
		}
		// default html input widget
		$input = new Html_Input();
		$input->setAttribute("name", reset($objects));
		return $input;
	}

	//------------------------------------------------------------------------------------ getFeature
	/**
	 * Returns template's feature method name
	 *
	 * @param $template Html_Template
	 * @param $objects  mixed[]
	 * @return Displayable
	 */
	public static function getFeature(Html_Template $template, $objects)
	{
		return new Displayable($template->getFeature(), Displayable::TYPE_METHOD);
	}

	//---------------------------------------------------------------------------------------- getHas
	/**
	 * Returns true if the element is not empty
	 * (usefull for conditions on arrays)
	 *
	 * @param $template Html_Template
	 * @param $objects mixed[]
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
	 * @param $template Html_Template
	 * @param $objects mixed[]
	 * @return object
	 */
	public static function getObject(Html_Template $template, $objects)
	{
		$object = null;
		foreach ($objects as $object) {
			if (is_object($object)) {
				break;
			}
		}
		return $object;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Returns object's properties, and their display and value
	 *
	 * @param $template Html_Template
	 * @param $objects mixed[]
	 * @return Reflection_Property_Value[]
	 */
	public static function getProperties(Html_Template $template, $objects)
	{
		$object = reset($objects);
		$properties_filter = $template->getParameter("properties_filter");
		$class = Reflection_Class::getInstanceOf($object);
		$properties = $class->accessProperties();
		$result_properties = array();
		foreach ($properties as $property_name => $property) {
			if (!$property->isStatic()) {
				if (!isset($properties_filter) || in_array($property_name, $properties_filter)) {
					$result_properties[$property_name] = new Reflection_Property_Value($property, $object);
				}
			}
		}
		$class->accessPropertiesDone();
		return $result_properties;
	}

	//------------------------------------------------------------------------ getPropertiesOutOfTabs
	/**
	 * Returns object's properties, and their display and value, but only if they are not already into a tab
	 *
	 * @param $template Html_Template
	 * @param $objects mixed[]
	 * @return Reflection_Property_Value[]
	 */
	public static function getPropertiesOutOfTabs(Html_Template $template, $objects)
	{
		$properties = array();
		foreach (self::getProperties($template, $objects) as $property_name => $property) {
			if (!$property->isStatic()) {
				if (!$property->tab_path) {
					$properties[$property_name] = $property;
				}
			}
		}
		return $properties;
	}

	//--------------------------------------------------------------------------------- getRootObject
	/**
	 * Returns root object from templating tree
	 *
	 * @param $template Html_Template
	 * @param $objects mixed[]
	 * @return object
	 */
	public static function getRootObject(Html_Template $template, $objects)
	{
		$object = null;
		foreach (array_reverse($objects) as $object) {
			if (is_object($object)) {
				break;
			}
		}
		return $object;
	}

	//---------------------------------------------------------------------------------------- getTop
	/**
	 * Returns template's top object
	 * (use it inside of loops)
	 *
	 * @param $template Html_Template
	 * @param $objects mixed[]
	 * @return object
	 */
	public static function getTop(Html_Template $template, $objects)
	{
		return $template->getObject();
	}

	//--------------------------------------------------------------------------- toEditPropertyExtra
	/**
	 * Gets property extra data needed for edit widget
	 *
	 * @param $class_name string
	 * @param $property   Reflection_Property_Value|Reflection_Property|string
	 * @return mixed[] Reflection_Property $property, string $property path, mixed $value
	 */
	private static function toEditPropertyExtra($class_name, $property)
	{
		if ($property instanceof Reflection_Property_Value) {
			$property_path = $property->path;
			$value = $property->value();
		}
		elseif ($property instanceof Reflection_Property) {
			$property_path = $property->name;
			$value = "";
		}
		else {
			$property_path = $property;
			$value = "";
			$property = Reflection_Property::getInstanceOf($class_name, $property);
		}
		return array($property, $property_path, $value);
	}

}
