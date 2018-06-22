<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure;
use ITRocks\Framework\Layout\Structure\Field\Property;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Page;
use ITRocks\Framework\Property\Reflection_Property;

/**
 * Scan all property zones for groups and repetitive
 * If some repeat zones are not already inside a group : generate the group
 */
class Generate_Groups
{

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

	//------------------------------------------------------------------------------------ $structure
	/**
	 * @var Structure
	 */
	protected $structure;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $structure Structure
	 */
	public function __construct(Structure $structure)
	{
		$this->structure = $structure;
	}

	//--------------------------------------------------------------------------------------- element
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $element Property
	 */
	protected function element(Property $element)
	{
		$class_name = $this->structure->class_name;
		/** @var $last_group Group|null */
		$last_group    = null;
		$property_path = '';
		foreach (explode(DOT, $element->property_path) as $property_name) {
			if ($property_path) {
				$property_path .= DOT;
			}
			$property_path .= $property_name;
			/** @noinspection PhpUnhandledExceptionInspection property path must be valid */
			$property = new Reflection_Property($class_name, $property_name);
			$type     = $property->getType();
			if ($type->isMultiple() && !$element->group) {
				$group = $this->elementGroup($element, $property_path);
				if ($last_group) {
					$last_group->group = $group;
				}
				$last_group = $group;
			}
			$class_name = $type->getElementTypeAsString();
		}
		if ($last_group) {
			$element->group = $last_group;
		}
	}

	//---------------------------------------------------------------------------------- elementGroup
	/**
	 * @param $element       Property
	 * @param $property_path string 'property.path'
	 * @return Group
	 */
	protected function elementGroup(Property $element, $property_path)
	{
		if (isset($this->groups[$property_path])) {
			$group = $this->groups[$property_path];
		}
		else {
			$group = new Group($element->page);
			$group->elements[]    = $element;
			$group->left          = 99999;
			$group->property_path = $property_path;
			$group->top           = 99999;
			$this->groups[$property_path] = $group;
		}
		// begin with new height and width growth
		$group->height = max($group->height, $element->top  - $group->top  + $element->height);
		$group->width  = max($group->width,  $element->left - $group->left + $element->width);
		// then enlarge left an top
		$group->left = min($group->left, $element->left);
		$group->top  = min($group->top,  $element->top);
		return $group;
	}

	//---------------------------------------------------------------------------------- enlargeGroup
	/**
	 * Automatically the vertical limit of each generated group
	 *
	 * @param $group Group
	 */
	protected function enlargeGroup(Group $group)
	{
		$minimal_bottom = $group->page->height - $this->page_margin;
		foreach ($group->page->elements as $element) {
			if (
				// only elements outside of the group
				!$element->insideGroup($group)
				&& ($element->top > $group->bottom())
				&& ($element->right() >= $group->left)
				&& ($element->left <= $group->right())
			) {
				$minimal_bottom = min($minimal_bottom, $element->top - $this->bottom_margin);
			}
		}
		$minimal_height = $minimal_bottom - $group->top;
		$group->height = max($group->height, $minimal_height);
	}

	//------------------------------------------------------------------------------------------ page
	/**
	 * @param $page Page
	 */
	protected function page(Page $page)
	{
		$this->groups = [];
		foreach ($page->elements as $element) {
			if ($element instanceof Property) {
				$this->element($element);
			}
		}
		foreach ($this->groups as $group) {
			$this->enlargeGroup($group);
			array_push($group->elements, $group);
		}
	}

	//------------------------------------------------------------------------------------------- run
	public function run()
	{
		foreach ($this->structure->pages as $page) {
			$this->page($page);
		}
	}

}
