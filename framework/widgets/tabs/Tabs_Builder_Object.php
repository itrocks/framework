<?php
namespace SAF\Framework;

abstract class Tabs_Builder_Object extends Tabs_Builder_Class
{

	//----------------------------------------------------------------------------------------- build
	/**
	 * Build tabs containing object propertie
	 *
	 * This fills in properties "display" and "value" special properties, usefull ie for Html_Template_Funcs
	 *
	 * @param object $class
	 * @return multitype:Tab
	 */
	public static function build($object)
	{
		$class = Reflection_Class::getInstanceOf(get_class($object));
		$tab_annotations = $class->getAnnotation("group");
		$properties = $class->accessProperties();
		foreach ($properties as $property_name => $property) {
			$property->display = Names::propertyToDisplay($property_name);
			$property->value = $object->$property_name;
		}
		$class->accessPropertiesDone();
		return parent::buildProperties($properties, $tab_annotations);
	}

}
