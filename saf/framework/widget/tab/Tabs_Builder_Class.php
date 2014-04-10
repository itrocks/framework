<?php
namespace SAF\Framework\Widget\Tab;

use SAF\Framework\Reflection\Annotation\Class_\Group_Annotation;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Widget\Tab;

/**
 * Tabs builder : build tabs for a class
 */
abstract class Tabs_Builder_Class
{

	//----------------------------------------------------------------------------------------- build
	/**
	 * Build tabs containing class properties
	 *
	 * @param $class Reflection_Class
	 * @return Tab[] Tabs will contain Reflection_Property[] as content
	 */
	public static function build(Reflection_Class $class)
	{
		/** @var $group_annotations Group_Annotation[] */
		$group_annotations = $class->getAnnotations('group');
		$properties = $class->getAllProperties();
		return self::buildProperties($properties, $group_annotations);
	}

	//------------------------------------------------------------------------------- buildProperties
	/**
	 * Build tabs containing class properties
	 *
	 * @param $properties        Reflection_Property[]
	 * @param $group_annotations Group_Annotation[]
	 * @return Tab[]
	 */
	protected static function buildProperties($properties, $group_annotations)
	{
		$root_tab = new Tab();
		if (!empty($group_annotations)) {
			foreach ($group_annotations as $group_annotation) {
				$tab = $root_tab;
				foreach (explode(DOT, $group_annotation->name) as $tab_name) {
					if (is_numeric($tab_name)) {
						if (empty($tab->columns)) {
							if (!empty($tab->content)) {
								$tab->columns[0] = new Tab(0, $tab->content);
								$tab->content = [];
							}
						}
						if (!isset($tab->columns[$tab_name])) {
							$tab->columns[$tab_name] = new Tab($tab_name, []);
						}
						$tab = $tab->columns[$tab_name];
					}
					else {
						if (!isset($tab->includes[$tab_name])) {
							$tab->includes[$tab_name] = new Tab($tab_name, []);
						}
						$tab = $tab->includes[$tab_name];
					}
				}
				if (!empty($tab->columns)) {
					if (!isset($tab->columns[0])) {
						$tab->columns[0] = new Tab(0, []);
						ksort($tab->columns);
					}
					$tab = $tab->columns[0];
				}
				$tab->add(self::getProperties($properties, $group_annotation->value));
			}
		}
		return $root_tab->includes;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Filter class properties using an array of properties names
	 *
	 * @param $properties     Reflection_Property[]
	 * @param $property_names string[]
	 * @return Reflection_Property[]
	 */
	private static function getProperties($properties, $property_names)
	{
		$result = [];
		foreach ($property_names as $property_name) {
			if (isset($properties[$property_name])) {
				$result[$property_name] = $properties[$property_name];
			}
		}
		return $result;
	}

}
