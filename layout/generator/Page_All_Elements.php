<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Page;

/**
 * Elements into from page 'A' (all) must be copied into all pages
 */
class Page_All_Elements
{
	use Has_Structure;

	//----------------------------------------------------------- copyElementsFromPageAllToOtherPages
	protected function copyElementsFromPageAllToOtherPages()
	{
		foreach ($this->structure->pages as $all_page_key => $all_page) {
			if ($all_page->number === Page::ALL) {
				$copied = false;
				foreach ($this->structure->pages as $page) {
					if ($page->number !== Page::ALL) {
						foreach ($all_page::ALL_ELEMENT_PROPERTIES as $element_property) {
							foreach ($all_page->$element_property as $element) {
								array_push($page->$element_property, clone $element);
							}
						}
						$copied = true;
					}
				}
				if ($copied) {
					unset($this->structure->pages[$all_page_key]);
				}
			}
		}
	}

	//------------------------------------------------------------------------- pageAloneIsMiddlePage
	/**
	 * When there is only one page : set it as a middle page to apply it every time
	 */
	protected function pageAloneIsMiddlePage()
	{
		if (count($this->structure->pages) === 1) {
			reset($this->structure->pages)->number = Page::MIDDLE;
		}
	}

	//------------------------------------------------------------------------------------------- run
	public function run()
	{
		$this->copyElementsFromPageAllToOtherPages();
		$this->pageAloneIsMiddlePage();
	}

}
