<?php
namespace SAF\Framework;

abstract class Tabs_Builder_Object extends Tabs_Builder_Class
{

	//----------------------------------------------------------------------------------- buildObject
	/**
	 * Build tabs containing object properties
	 *
	 * This fills in properties "display" and "value" special properties, usefull ie for Html_Template_Funcs
	 *
	 * @param $object            object
	 * @param $filter_properties string[]
	 * @return Tab[]
	 */
	public static function buildObject($object, $filter_properties = null)
	{
		$class = Reflection_Class::getInstanceOf($object);
		/** @var $group_annotations Class_Group_Annotation[] */
		$group_annotations = $class->getAnnotations("group");
		$properties = $class->accessProperties();
		foreach ($properties as $property_name => $property) {
			if (!isset($filter_properties) || in_array($property_name, $filter_properties)) {
				$properties[$property_name] = new Reflection_Property_Value($property, $object);
			}
			else {
				unset($properties[$property_name]);
			}
		}
		$class->accessPropertiesDone();
		return parent::buildProperties($properties, $group_annotations);
	}

}
