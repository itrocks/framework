<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Has_Structure;

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
	protected array $links;

	//----------------------------------------------------------------------------------------- group
	/**
	 * @param $group Group
	 */
	protected function group(Group $group) : void
	{
		$this->links[$group->property_path][$group->page->number] = $group;
		$group->links =& $this->links[$group->property_path];
	}

	//------------------------------------------------------------------------------------------- run
	public function run() : void
	{
		foreach ($this->structure->pages as $page) {
			foreach ($page->groups as $group) {
				if (!$group->links) {
					$this->group($group);
				}
			}
		}
	}

}
