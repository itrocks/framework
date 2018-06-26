<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Has_Structure;
use ITRocks\Framework\Layout\Structure\Page;

/**
 * Count pages
 */
class Count_Pages
{
	use Has_Structure;

	//----------------------------------------------------------------------------------------- group
	/**
	 * Add pages depending on room needed by the group
	 *
	 * @param $group Group
	 */
	protected function group(Group $group)
	{
		$structure        = $this->structure;
		$page_number      = 1;
		$pages_count      = $structure->pages_count;
		$page             = $structure->page($page_number, $pages_count);
		$page_height      = $group->heightOnPage($page);
		$available_height = $page_height;
		foreach ($group->iterations as $iteration_key => $iteration) {
			if (($available_height < $iteration->height) && ($page_number === $pages_count)) {
				$pages_count      ++;
				$page             = $structure->page($page_number, $pages_count);
				$new_page_height  = $group->heightOnPage($page);
				$available_height = $available_height - $page_height + $new_page_height;
				$page_height      = $new_page_height;
			}
			if ($available_height < $iteration->height) {
				$page_number      ++;
				$page             = $structure->page($page_number, $pages_count);
				$page_height      = $group->heightOnPage($page);
				$available_height = $page_height;
			}
			$available_height -= $iteration->height;
		}
		$structure->pages_count = $pages_count;
	}

	//----------------------------------------------------------------------------- minimumPagesCount
	/**
	 * Calculate the minimum pages count
	 *
	 * @example
	 * - one page or 'unique' page exists => 1
	 * - all other cases => 2
	 */
	protected function minimumPagesCount()
	{
		$pages = $this->structure->pages;
		if (isset($pages[Page::UNIQUE]) || (count($pages) === 1)) {
			return 1;
		}
		return 2;
	}

	//------------------------------------------------------------------------------------------- run
	public function run()
	{
		$this->structure->pages_count = $this->minimumPagesCount();
		foreach ($this->structure->pages as $page) {
			foreach ($page->groups as $group) {
				if ($group === reset($group->links)) {
					$this->group($group);
				}
			}
		}
	}

}
