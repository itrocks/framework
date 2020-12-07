<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Has_Structure;
use ITRocks\Framework\Layout\Structure\Page;

/**
 * Dispatch group data on pages : all data that is lower than group bottom must go next page
 *
 * This also decides if we use unique page / first + last pages / first + middle + last pages
 */
class Dispatch_Iterations_On_Pages
{
	use Has_Structure;

	//---------------------------------------------------------------------------------------- $pages
	/**
	 * Generated final pages
	 *
	 * This generator starts with $this->structure->pages that are models
	 * It ends with $this->structure->pages containing real pages
	 * These real pages are stored here during the generation process
	 *
	 * @var Page[]
	 */
	protected $pages = [];

	//----------------------------------------------------------------------------------------- group
	/**
	 * @param $group Group
	 */
	protected function group(Group $group)
	{
		$iterations        = $group->iterations;
		$group->iterations = [];
		$page_number       = 0;
		$page_group        = $this->nextPageGroup($group, $page_number);
		$shift_top         = $group->top - $page_group->top;
		$page_group_bottom = $page_group->bottom();

		foreach ($iterations as $iteration) {
			$iteration_bottom = $iteration->top + $iteration->height - $shift_top;
			if ($iteration_bottom > $page_group_bottom) {
				$page_group = $this->nextPageGroup($group, $page_number);
				$shift_top  = $iteration->top - $page_group->top;
			}
			$iteration->up($shift_top);
			$page_group->iterations[] = $iteration;
		}

		if (count($this->pages) < $this->structure->pages_count) {
			$this->nextPageGroup($group, $page_number);
		}
	}

	//--------------------------------------------------------------------------------- nextPageGroup
	/**
	 * @param $group       Group
	 * @param $page_number integer Incremented 1..n times : until next page with a linked group found
	 * @return Group The linked group for the found page
	 */
	protected function nextPageGroup(Group $group, int &$page_number) : Group
	{
		do {
			$page_number ++;
			$page        = $this->page($page_number);
			$page_group  = $group->linkOnPage($page);
			$this->pages[$page->number] = $page;
		}
		while (!$page_group);

		return $page_group;
	}

	//------------------------------------------------------------------------------------------ page
	/**
	 * Get the real page for this page number
	 *
	 * @param $page_number integer
	 * @return ?Page
	 */
	protected function page(int $page_number) : ?Page
	{
		if (isset($this->pages[$page_number])) {
			return $this->pages[$page_number];
		}
		$model_page = $this->structure->page($page_number);
		// get unique page without cloning it
		if ($model_page->number === strval($page_number)) {
			$page         = $model_page;
			$page->number = $page_number;
		}
		// create page by cloning
		else {
			$page = $model_page->cloneWithNumber($page_number);
		}
		$this->pages[$page->number] = $page;
		return $page;
	}

	//------------------------------------------------------------------------------------------- run
	public function run()
	{
		foreach ($this->structure->pages as $page) {
			foreach ($page->groups as $group) {
				if ($group === reset($group->links)) {
					$this->group($group);
				}
			}
		}
		$this->structure->pages = $this->pages
			?: ($this->structure->pages ? [1 => $this->structure->page(1)->cloneWithNumber(1)] : []);
	}

}
