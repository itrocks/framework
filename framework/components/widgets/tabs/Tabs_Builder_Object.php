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
	 * @param $object object
	 * @return Tab[]
	 */
	public static function build($object)
	{
		$class = Reflection_Class::getInstanceOf(get_class($object));
		/** @var $group_annotations Class_Group_Annotation[] */
		$group_annotations = $class->getAnnotations("group");
		$properties = $class->accessProperties();
		foreach ($properties as $property_name => $property) {
			$properties[$property_name] = new Reflection_Property_Value($property, $object);
		}
		$class->accessPropertiesDone();
		return parent::buildProperties($properties, $group_annotations);
	}

}
