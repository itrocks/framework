<?php
namespace SAF\Framework\Widget\Tab;

use SAF\Framework\Reflection\Annotation\Class_\Group_Annotation;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property_Value;
use SAF\Framework\Widget\Tab;

/**
 * Tabs builder : build tabs for an object
 */
abstract class Tabs_Builder_Object extends Tabs_Builder_Class
{

	//----------------------------------------------------------------------------------- buildObject
	/**
	 * Build tabs containing object properties
	 *
	 * This fills in properties 'display' and 'value' special properties, usefull ie for
	 * Html_Template_Functions
	 *
	 * @param $object            object
	 * @param $filter_properties string[]
	 * @return Tab[] tabs will contain Reflection_Property_Value[] as content
	 */
	public static function buildObject($object, $filter_properties = null)
	{
		$class = new Reflection_Class(get_class($object));
		/** @var $group_annotations Group_Annotation[] */
		$group_annotations = $class->getAnnotations('group');
		self::mergeGroups($group_annotations);
		$properties = $class->accessProperties();
		foreach ($properties as $property_name => $property) {
			if (!isset($filter_properties) || in_array($property_name, $filter_properties)) {
				$property = new Reflection_Property_Value(
					$property->class, $property->name, $object, false, true
				);
				$property->final_class = $class->name;
				$properties[$property_name] = $property;
			}
			else {
				unset($properties[$property_name]);
			}
		}
		return parent::buildProperties($properties, $group_annotations);
	}

	//----------------------------------------------------------------------------------- mergeGroups
	/**
	 * @param $groups Group_Annotation[]
	 */
	private static function mergeGroups(&$groups)
	{
		// merge groups that have the same name
		$merged = [];
		foreach ($groups as $group) {
			if (isset($merged[$group->name])) {
				$merged[$group->name]->value = array_merge($merged[$group->name]->value, $group->value);
			}
			else {
				$merged[$group->name] = $group;
			}
		}
		$groups = $merged;
		// get customized group if alone, and _top and _middle groups
		/** @var $customized Group_Annotation */
		/** @var $middle Group_Annotation */
		/** @var $top Group_Annotation */
		$customized = null;
		$custom_key = $middle_key = -1;
		foreach ($groups as $key => $group) {
			if ($group->name[0] != '_') {
				if (isset($customized)) {
					$customized = false;
					break;
				}
				$customized = $group;
				$custom_key = $key;
			}
			elseif ($group->name == '_top') {
				$top = $group;
			}
			elseif ($group->name == '_middle') {
				$middle = $group;
				$middle_key = $key;
			}
		}
		// if customized group is alone : merge it and _top and _middle groups into _top
		if ($customized) {
			if (!isset($top)) {
				$top = $customized;
				$top->name = '_top';
			}
			else {
				$top->value = array_merge($top->value, $customized->value);
				unset($groups[$custom_key]);
			}
			if (isset($middle)) {
				$top->value = array_merge($top->value, $middle->value);
				unset($groups[$middle_key]);
			}
		}
	}

}
