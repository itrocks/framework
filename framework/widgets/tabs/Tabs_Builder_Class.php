<?php
namespace SAF\Framework;

abstract class Tabs_Builder_Class
{

	//----------------------------------------------------------------------------------------- build
	/**
	 * Build tabs containing class properties
	 *
	 * @param Reflection_Class $class
	 * @return multitype:Tab
	 */
	public static function build(Reflection_Class $class)
	{
		$properties = $class->getAllProperties();
		$tab_annotations = $class->getAnnotation("tab");
		if ($tab_annotations instanceof Class_Tab_Annotation) {
			foreach ($tab_annotations as $tab_annotation) {
				$tabs[$tab_annotation->name] = new Tab(
					$tab_annotation->name,
					self::getProperties($properties, $tab_annotation->value)
				);
			}
			return $tabs;
		}
		else {
			$tabs = array(new Tab("_top", $properties));
		}
		return $tabs;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Filter class properties using an array of properties names
	 *
	 * @param multitype:Reflection_Property $properties
	 * @param multitype:string $property_names
	 * @return multitype:Reflection_Property
	 */
	private static function getProperties($properties, $property_names)
	{
		$result = array();
		foreach ($property_names as $property_name) {
			if (isset($properties[$property_name])) {
				$result[$property_name] = $properties[$property_name];
			}
		}
		return $result;
	}

}
