<?php
namespace ITRocks\Framework\Layout;

use ITRocks\Framework\Layout\Print_Model\Page;

/**
 * Print model
 *
 * @override pages @var Page[]
 * @property Page[] pages
 * @see Page
 * @store_name print_models
 */
class Print_Model extends Model
{

	//-------------------------------------------------------------------------------------- getPages
	/**
	 * @return Page[]
	 */
	protected function getPages()
	{
		$this->pages = parent::getPages();
		/** @var $model Print_Model */
		if (!$this->pages) {
			$this->pages = [
				$this->newPage(Page::UNIQUE),
				$this->newPage(Page::FIRST),
				$this->newPage(Page::MIDDLE),
				$this->newPage(Page::LAST),
				$this->newPage(Page::ALL)
			];
		}
		return $this->pages;
	}

}
