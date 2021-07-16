<?php
namespace ITRocks\Framework\Layout;

use ITRocks\Framework\Layout\Print_Model\Page;
use ITRocks\Framework\Layout\Print_Model\Status;

/**
 * Print model
 *
 * @feature
 * @override pages @var Page[]
 * @property Page[] pages
 * @see Page
 * @store_name print_models
 */
class Print_Model extends Model
{

	//--------------------------------------------------------------------------------------- $status
	/**
	 * @default defaultStatus
	 * @user readonly
	 * @values Status::const
	 * @var string
	 */
	public $status;

	//--------------------------------------------------------------------------------- defaultStatus
	/**
	 * @noinspection PhpUnused @default
	 * @return string
	 * @return_constant
	 */
	public function defaultStatus() : string
	{
		return Status::CUSTOMIZED;
	}
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
