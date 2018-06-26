<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Group;
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
		$done_height = 0;

		// get first page containing group
		$page_number = 1;
		$page        = $this->page($page_number);
		$page_group  = $group->linkOnPage($page);
		while (!$page_group) {
			$page_number ++;
			$page        = $this->structure->page($page_number);
			$page_group  = $group->linkOnPage($page);
		}

		foreach ($group->iterations as $iteration) {

		}
	}

	//------------------------------------------------------------------------------------------ page
	/**
	 * Get the real page for this page number
	 *
	 * @param $page_number integer
	 * @return Page
	 */
	public function page($page_number)
	{
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
		$this->structure->pages = $this->pages;
	}

}
