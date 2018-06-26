<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Draw\Snap_Line;
use ITRocks\Framework\Layout\Structure\Field\Property;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Has_Structure;
use ITRocks\Framework\Layout\Structure\Page;
use ITRocks\Framework\Property\Reflection_Property;

/**
 * Scan all property zones for groups and repetitive
 * If some repeat zones are not already inside a group : generate the group
 */
class Generate_Groups
{
	use Has_Structure;

	//-------------------------------------------------------------------------------- $bottom_margin
	/**
	 * Default margin below automatically created groups (mm)
	 *
	 * This is the separation between the bottom of the group and the top of the highest element below
	 *
	 * @var float
	 */
	public $bottom_margin = 5;

	//--------------------------------------------------------------------------------------- $groups
	/**
	 * Groups into the current page
	 *
	 * @var Group[] key is a 'property.path' string
	 */
	protected $groups;

	//---------------------------------------------------------------------------------- $page_margin
	/**
	 * Default margin for the page (mm)
	 *
	 * You can't grow automatic groups below this limit
	 *
	 * @var float
	 */
	public $page_margin = 10;

	//---------------------------------------------------------------------------------- enlargeGroup
	/**
	 * Automatically the vertical limit of each generated group
	 *
	 * @param $group Group
	 */
	protected function enlargeGroup(Group $group)
	{
		$minimal_bottom = $group->page->height - $this->page_margin;
		foreach ($group->page->allElements() as $element) {
			if (
				// only elements outside of the group
				!$element->insideGroup($group)
				&& ($element->top > $group->bottom())
				&& (
					($element instanceof Snap_Line)
					|| (
						($element->right() >= $group->left)
						&& ($element->left <= $group->right())
					)
				)
			) {
				$element_top = $element->top;
				if (!$element instanceof Snap_Line) {
					$element_top -= $this->bottom_margin;
				}
				$minimal_bottom = min($minimal_bottom, $element_top);
			}
		}
		$minimal_height = $minimal_bottom - $group->top;
		$group->height  = max($group->height, $minimal_height);
	}

	//------------------------------------------------------------------------------------------ page
	/**
	 * @param $page Page
	 */
	protected function page(Page $page)
	{
		$this->groups = [];
		foreach ($page->properties as $property_key => $property) {
			if ($this->property($property)) {
				unset($page->properties[$property_key]);
			}
		}
		foreach ($this->groups as $group) {
			$this->enlargeGroup($group);
			array_push($page->groups, $group);
		}
	}

	//-------------------------------------------------------------------------------------- property
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $property Property
	 * @return boolean true if the property was stored into a group, else false
	 */
	protected function property(Property $property)
	{
		$class_name = $this->structure->class_name;
		/** @var $last_group Group|null */
		$last_group    = null;
		$property_path = '';
		foreach (explode(DOT, $property->property_path) as $property_name) {
			if ($property_path) {
				$property_path .= DOT;
			}
			$property_path .= $property_name;
			/** @noinspection PhpUnhandledExceptionInspection property path must be valid */
			$type = (new Reflection_Property($class_name, $property_name))->getType();
			if ($type->isMultiple() && !$property->group) {
				$group = $this->propertyGroup($property, $property_path);
				if ($last_group) {
					$group->group = $last_group;
				}
				$last_group = $group;
			}
			$class_name = $type->getElementTypeAsString();
		}
		if ($last_group) {
			$property->group = $last_group;
			return true;
		}
		return false;
	}

	//--------------------------------------------------------------------------------- propertyGroup
	/**
	 * @param $property      Property
	 * @param $property_path string 'property.path'
	 * @return Group
	 */
	protected function propertyGroup(Property $property, $property_path)
	{
		if (isset($this->groups[$property_path])) {
			$group = $this->groups[$property_path];
			// first enlarge size
			$group->height = max($group->height, $property->top  - $group->top  + $property->height);
			$group->width  = max($group->width,  $property->left - $group->left + $property->width);
			// then move position
			$group->left   = min($group->left, $property->left);
			$group->top    = min($group->top,  $property->top);
		}
		else {
			$group                        = new Group($property->page);
			$group->height                = $property->height;
			$group->left                  = $property->left;
			$group->property_path         = $property_path;
			$group->top                   = $property->top;
			$group->width                 = $property->width;
			$this->groups[$property_path] = $group;
		}
		$group->properties[] = $property;
		return $group;
	}

	//------------------------------------------------------------------------------------------- run
	public function run()
	{
		foreach ($this->structure->pages as $page) {
			$this->page($page);
		}
	}

}
