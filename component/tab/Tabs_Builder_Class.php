<?php
namespace ITRocks\Framework\Component\Tab;

use ITRocks\Framework\Component\Tab;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Attribute\Class_\Group;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Set;

/**
 * Tabs builder : build tabs for a class
 */
class Tabs_Builder_Class
{

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var $class Reflection_Class
	 */
	protected Reflection_Class $class;

	//----------------------------------------------------------------------------------------- build
	/**
	 * Build tabs containing class properties
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object            object|string object or class name/reflection
	 * @param $filter_properties string[]
	 * @return Tab[] Tabs will contain Reflection_Property[] as content
	 */
	public function build(object|string $object, array $filter_properties = null)
		: array
	{
		/** @noinspection PhpUnhandledExceptionInspection class must be valid */
		$this->class = ($object instanceof Reflection_Class)
			? $object
			: new Reflection_Class($object);
		$groups = Group::of($this->class);
		$this->removeDuplicateProperties($groups);
		$this->mergeGroups($groups);
		$this->sortGroups($groups);
		$properties = $this->groupsToProperties($object, $groups, $filter_properties);
		if ($filter_properties) {
			$properties_set = new Set(Reflection_Property::class, $properties);
			$properties     = $properties_set->filterAndSort($filter_properties);
		}
		return $this->buildProperties($properties, $groups);
	}

	//------------------------------------------------------------------------------- buildProperties
	/**
	 * Build tabs containing class properties
	 *
	 * @param $properties Reflection_Property[]
	 * @param $groups     Group[]
	 * @return Tab[]
	 */
	protected function buildProperties(array $properties, array $groups) : array
	{
		$root_tab = new Tab();
		if (!$groups) {
			return $root_tab->includes;
		}
		foreach ($groups as $group) {
			$tab = $root_tab;
			foreach (explode(DOT, $group->name) as $tab_name) {
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
			$tab->add($this->getProperties($properties, $group->values));
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
	protected function getProperties(array $properties, array $property_names) : array
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object        object|string object or class name
	 * @param $property_path string
	 * @return Reflection_Property
	 */
	protected function getProperty(object|string $object, string $property_path) : Reflection_Property
	{
		/** @noinspection PhpUnhandledExceptionInspection $object must be valid */
		return new Reflection_Property($object, $property_path);
	}

	//---------------------------------------------------------------------------- groupsToProperties
	/**
	 * @param $object            object|string object or class name
	 * @param $groups            Group[]
	 * @param $filter_properties string[] if empty, then get all properties
	 * @return Reflection_Property[]
	 */
	protected function groupsToProperties(
		object|string $object, array $groups, array $filter_properties
	) : array
	{
		if ($object instanceof Reflection_Class) {
			$object = $object->name;
		}
		$properties = [];
		foreach ($groups as $group) {
			foreach ($group->values as $property_path) {
				if (in_array($property_path, $filter_properties) || !$filter_properties) {
					$property                   = $this->getProperty($object, $property_path);
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
	 * @param $groups Group[]
	 */
	protected function mergeGroups(array &$groups) : void
	{
		/** @var $merged Group[] Merge groups that have the same name */
		$merged = [];
		foreach ($groups as $group) {
			if (isset($merged[$group->name])) {
				$merged[$group->name]->values = array_merge($merged[$group->name]->values, $group->values);
			}
			else {
				$merged[$group->name] = $group;
			}
		}
		$groups = $merged;

		// get customized group if alone, and _top and _middle groups
		$customized = null;
		$custom_key = $middle_key = -1;
		foreach ($groups as $key => $group) {
			if ($group->name[0] !== '_') {
				if (isset($customized)) {
					$customized = null;
					break;
				}
				$customized = $group;
				$custom_key = $key;
			}
			elseif ($group->name === '_top') {
				$top = $group;
			}
			elseif ($group->name === '_middle') {
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
				$top->values = array_merge($top->values, $customized->values);
				unset($groups[$custom_key]);
			}
			if (isset($middle)) {
				$top->values = array_merge($top->values, $middle->values);
				unset($groups[$middle_key]);
			}
		}
	}

	//--------------------------------------------------------------------- removeDuplicateProperties
	/**
	 * Remove duplicate property paths : a property cannot be into two groups at the same time
	 * Properties remain into the first group, which are those declared at the highest level
	 *
	 * @param $groups Group[]
	 */
	protected function removeDuplicateProperties(array &$groups) : void
	{
		$already = [];
		foreach ($groups as $key => $group) {
			foreach ($group->values as $property_path) {
				if (isset($already[$property_path])) {
					$group->remove($property_path);
				}
				else {
					$already[$property_path] = true;
				}
			}
			if (!$group->values) {
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
	 * @param $groups Group[] Key is the name of the group
	 */
	protected function sortGroups(array &$groups) : void
	{
		$sorted_groups = [];
		$groups_order  = $this->class->getListAnnotation('groups_order')->values();
		foreach ($groups_order as $group_name) {
			if (isset($groups[$group_name])) {
				$sorted_groups[$group_name] = $groups[$group_name];
				unset($groups[$group_name]);
			}
		}
		uasort($groups, function(Group $group1, Group $group2) {
			return strcmp(Loc::tr($group1->name), Loc::tr($group2->name));
		});
		$groups = array_merge($sorted_groups, $groups);
	}

}
