<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Draw\Snap_Line;
use ITRocks\Framework\Layout\Structure\Field;
use ITRocks\Framework\Layout\Structure\Field\Property;
use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Has_Structure;
use ITRocks\Framework\Layout\Structure\Page;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Property\Reflection_Property;
use ITRocks\Framework\Tools\Names;

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
	public float $bottom_margin = .5;

	//--------------------------------------------------------------------------------------- $groups
	/**
	 * Groups into the current page
	 *
	 * @var Group[] key is a 'property.path' string
	 */
	protected array $groups;

	//---------------------------------------------------------------------------------- $page_margin
	/**
	 * Default margin for the page (mm)
	 *
	 * You can't grow automatic groups below this limit
	 *
	 * @var float
	 */
	public float $page_margin = 10;

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
				($element->top > $group->bottom())
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
		foreach ($page->elements as $element_key => $element) {
			if (
				($element instanceof Text)
				&& str_contains($element->text, '{')
				&& $this->text($element)
			) {
				unset($page->elements[$element_key]);
			}
		}
		foreach ($page->properties as $property_key => $property) {
			if ($this->property($property)) {
				unset($page->properties[$property_key]);
			}
		}
		foreach ($this->groups as $group) {
			$this->enlargeGroup($group);
			$page->groups[] = $group;
		}
	}

	//-------------------------------------------------------------------------------------- property
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $property Property
	 * @return boolean true if the property was stored into a group, else false
	 */
	protected function property(Property $property) : bool
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
			if (!property_exists($class_name, $property_name)) {
				return false;
			}
			/** @noinspection PhpUnhandledExceptionInspection property_exists */
			$type = (new Reflection_Property($class_name, $property_name))->getType();
			if ($type->isMultiple()) {
				$group = $this->propertyGroup($property, $property_path);
				if ($last_group) {
					$group->group = $last_group;
				}
				$last_group = $group;
			}
			$class_name = $type->getElementTypeAsString();
		}
		if ($last_group) {
			$last_group->properties[] = $property;
			$property->group          = $last_group;
			return true;
		}
		return false;
	}

	//--------------------------------------------------------------------------------- propertyGroup
	/**
	 * Store a field containing a property path into a group, if needed
	 *
	 * @param $field         Field
	 * @param $property_path string 'property.path'
	 * @return Group
	 */
	protected function propertyGroup(Field $field, string $property_path) : Group
	{
		if (isset($this->groups[$property_path])) {
			$group = $this->groups[$property_path];
			// first enlarge size
			$group->height = max($group->height, $field->top  - $group->top  + $field->height);
			$group->width  = max($group->width,  $field->left - $group->left + $field->width);
			// then move position
			$group->left   = min($group->left, $field->left);
			$group->top    = min($group->top,  $field->top);
		}
		else {
			$group                        = new Group($field->page);
			$group->height                = $field->height;
			$group->left                  = $field->left;
			$group->property_path         = $property_path;
			$group->top                   = $field->top;
			$group->width                 = $field->width;
			$this->groups[$property_path] = $group;
		}
		return $group;
	}

	//------------------------------------------------------------------------------------------- run
	public function run()
	{
		foreach ($this->structure->pages as $page) {
			$this->page($page);
		}
	}

	//------------------------------------------------------------------------------------------ text
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $text Text
	 * @return boolean
	 */
	protected function text(Text $text) : bool
	{
		/** @var $last_group Group|null */
		$last_group_count = 0;
		foreach ($text->propertyPaths() as $current_property_path) {
			if (in_array($current_property_path, Text_Templating::PAGE_PROPERTY_PATHS)) {
				continue;
			}
			$class_name    = $this->structure->class_name;
			$group_count   = 0;
			$last_group    = null;
			$property_path = '';
			foreach (explode(DOT, $current_property_path) as $property_name) {
				if (!property_exists($class_name, $property_name)) {
					$property_name = Names::displayToProperty(Loc::rtr(
						Names::propertyToDisplay($property_name),
						$class_name
					));
				}
				if ($property_path) {
					$property_path .= DOT;
				}
				$property_path .= $property_name;
				if (!property_exists($class_name, $property_name)) {
					return false;
				}
				/** @noinspection PhpUnhandledExceptionInspection property_exists */
				$type = (new Reflection_Property($class_name, $property_name))->getType();
				if ($type->isMultiple()) {
					$group = $this->propertyGroup($text, $property_path);
					if ($last_group) {
						$group->group = $last_group;
					}
					$last_group  = $group;
					$group_count ++;
				}
				$class_name = $type->getElementTypeAsString();
			}
			if ($last_group && ($group_count > $last_group_count)) {
				$last_group->elements[] = $text;
				$last_group_count       = $group_count;
				$text->group            = $last_group;
			}
		}
		return isset($text->group);
	}

}
