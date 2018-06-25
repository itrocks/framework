<?php
namespace ITRocks\Framework\Layout\Generator;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Page;

/**
 * Link groups through pages
 *
 * Groups with the same property_path will be linked
 */
class Link_Groups
{
	use Has_Structure;

	//---------------------------------------------------------------------------------------- $links
	/**
	 * @var array Group[][] : keys are property.path and page number
	 */
	protected $links;

	//----------------------------------------------------------------------------------------- group
	/**
	 * @param $group Group
	 */
	public function group(Group $group)
	{
		$this->links[$group->property_path][$group->page->number] = $group;
		$group->groups =& $this->links[$group->property_path];
	}

	//------------------------------------------------------------------------------------------ page
	/**
	 * @param $page Page
	 */
	public function page(Page $page)
	{
		foreach ($page->elements as $element) {
			if (($element instanceof Group) && !$element->groups) {
				$this->group($element);
			}
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
