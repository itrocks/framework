<?php
namespace ITRocks\Framework\Widget\Tab;

use ITRocks\Framework\Reflection\Annotation\Class_\Group_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Set;
use ITRocks\Framework\Widget\Tab;

/**
 * Tabs builder : build tabs for a class
 */
class Tabs_Builder_Class
{

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var $class Reflection_Class
	 */
	protected $class;

	//----------------------------------------------------------------------------------------- build
	/**
	 * Build tabs containing class properties
	 *
	 * @param $class             Reflection_Class
	 * @param $filter_properties string[]
	 * @return Tab[] Tabs will contain Reflection_Property[] as content
	 */
	public function build(Reflection_Class $class, array $filter_properties = null)
	{
		$this->class       = $class;
		$group_annotations = Group_Annotation::allOf($class);
		$this->removeDuplicateProperties($group_annotations);
		$this->mergeGroups($group_annotations);
		$this->sortGroups($group_annotations);
		$properties = $this->groupsToProperties($class->name, $group_annotations, $filter_properties);
		if ($filter_properties) {
			$properties_set = new Set(Reflection_Property::class, $properties);
			$properties     = $properties_set->filterAndSort($filter_properties);
		}
		return $this->buildProperties($properties, $group_annotations);
	}

	//------------------------------------------------------------------------------- buildProperties
	/**
	 * Build tabs containing class properties
	 *
	 * @param $properties        Reflection_Property[]
	 * @param $group_annotations Group_Annotation[]
	 * @return Tab[]
	 */
	protected function buildProperties(array $properties, array $group_annotations)
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
				$tab->add($this->getProperties($properties, $group_annotation->value));
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
	protected function getProperties(array $properties, array $property_names)
	{
		$result = [];
		foreach ($property_names as $property_name) {
			if (isset($properties[$property_name])) {
				$result[$property_name] = $properties[$property_name];
			}
		}
		return $result;
	}

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * @param $class_name    string
	 * @param $property_path string
	 * @return Reflection_Property
	 */
	protected function getProperty($class_name, $property_path)
	{
		return new Reflection_Property($class_name, $property_path);
	}

	//---------------------------------------------------------------------------- groupsToProperties
	/**
	 * @param $class_name        string
	 * @param $group_annotations Group_Annotation[]
	 * @param $filter_properties string[] if empty, then get all properties
	 * @return Reflection_Property[]
	 */
	protected function groupsToProperties(
		$class_name, array $group_annotations, array $filter_properties
	) {
		$properties = [];
		foreach ($group_annotations as $group) {
			foreach ($group->values() as $property_path) {
				if (in_array($property_path, $filter_properties) || !$filter_properties) {
					$property                   = $this->getProperty($class_name, $property_path);
					$properties[$property_path] = $property;
				}
			}
		}
		return $properties;
	}

	//----------------------------------------------------------------------------------- mergeGroups
	/**
	 * Merge group annotations that have the same name into one
	 *
	 * @param $groups Group_Annotation[]
	 */
	protected function mergeGroups(array &$groups)
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
					$customized = null;
					break;
				}
				$customized = $group;
				$custom_key = $key;
			}
			elseif ($group->name == '_top') {
				$top = $group;
			}
			elseif ($group->name == '_middle') {
				$middle     = $group;
				$middle_key = $key;
			}
		}

		// if customized group is alone : merge it and _top and _middle groups into _top
		if ($customized) {
			if (!isset($top)) {
				$top       = $customized;
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

	//--------------------------------------------------------------------- removeDuplicateProperties
	/**
	 * Remove duplicate property paths : a property cannot be into two groups at the same time
	 * Properties remain into the first group, which are those declared at the highest level
	 *
	 * @param $groups Group_Annotation[]
	 */
	protected function removeDuplicateProperties(array &$groups)
	{
		$already = [];
		foreach ($groups as $key => $group) {
			foreach ($group->values() as $property_path) {
				if (isset($already[$property_path])) {
					$group->remove($property_path);
				}
				else {
					$already[$property_path] = true;
				}
			}
			if (!$group->values()) {
				unset($groups[$key]);
			}
		}
	}

	//------------------------------------------------------------------------------------ sortGroups
	/**
	 * Sort groups alphabetically
	 *
	 * If some group names are into @groups_order, they will be ordered first, in the same order,
	 * and the trailing groups will come after, sorted alphabetically.
	 *
	 * @param array $groups Group_Annotation[] Key is the name of the group
	 */
	protected function sortGroups(array &$groups)
	{
		$sorted_groups = [];
		$groups_order  = $this->class->getListAnnotation('groups_order')->values();
		foreach ($groups_order as $group_name) {
			if (isset($groups[$group_name])) {
				$sorted_groups[$group_name] = $groups[$group_name];
				unset($groups[$group_name]);
			}
		}
		asort($groups);
		$groups = array_merge($sorted_groups, $groups);
	}

}
