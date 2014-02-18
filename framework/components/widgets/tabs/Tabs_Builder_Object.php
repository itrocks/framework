<?php
namespace SAF\Framework;

/**
 * Tabs builder : build tabs for an object
 */
abstract class Tabs_Builder_Object extends Tabs_Builder_Class
{

	//----------------------------------------------------------------------------------- buildObject
	/**
	 * Build tabs containing object properties
	 *
	 * This fills in properties "display" and "value" special properties, usefull ie for Html_Template_Functions
	 *
	 * @param $object            object
	 * @param $filter_properties string[]
	 * @return Tab[] tabs will contain Reflection_Property_Value[] as content
	 */
	public static function buildObject($object, $filter_properties = null)
	{
		$class = new Reflection_Class(get_class($object));
		/** @var $group_annotations Class_Group_Annotation[] */
		$group_annotations = $class->getAnnotations("group");
		$properties = $class->accessProperties();
		foreach ($properties as $property_name => $property) {
			if (!isset($filter_properties) || in_array($property_name, $filter_properties)) {
				$properties[$property_name] = new Reflection_Property_Value(
					$property->class, $property->name, $object
				);
			}
			else {
				unset($properties[$property_name]);
			}
		}
		return parent::buildProperties($properties, $group_annotations);
	}

}
