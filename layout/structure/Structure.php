<?php
namespace ITRocks\Framework\Layout;

use ITRocks\Framework\Layout\Structure\Page;

/**
 * This is the structure of the data to be output
 *
 * The layout generator takes a layout model and an object as input.
 * Then Layout\Generator::generate() generates a structure containing structured data.
 * This data contains coordinates and page numbers, and is ready to print / generate outputs.
 */
class Structure
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public string $class_name;

	//---------------------------------------------------------------------------------------- $pages
	/**
	 * @var Page[] Page[string $page_number] key is the page number or Page constant value
	 */
	public array $pages;

	//---------------------------------------------------------------------------------- $pages_count
	/**
	 * Pages count, calculated by Count_Pages
	 *
	 * @var integer
	 */
	public int $pages_count;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string|null
	 */
	public function __construct(string $class_name = null)
	{
		if (isset($class_name)) {
			$this->class_name = $class_name;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->dump();
	}

	//------------------------------------------------------------------------------------------ dump
	/**
	 * @return string
	 */
	public function dump() : string
	{
		$dump = $this->pages_count . ' PAGES' . LF . LF;
		foreach ($this->pages as $page) {
			$dump .= $page->dump() . LF;
		}
		return $dump;
	}

	//------------------------------------------------------------------------------------------ page
	/**
	 * Gets the page model that matches the page number and pages count
	 *
	 * @param $page_number integer absolute page number
	 * @param $pages_count integer|null default is $this->pages_count
	 * @param $pages       Page[] default is $this->pages
	 * @return Page
	 */
	public function page(int $page_number, int $pages_count = null, array $pages = []) : Page
	{
		if (!$pages) {
			$pages = $this->pages;
		}
		if (!$pages_count) {
			$pages_count = $this->pages_count;
			if (!$pages_count) {
				trigger_error('page was called without pages count', E_USER_ERROR);
			}
		}
		$negative    = strval($page_number - $pages_count - 1);
		$page_number = strval($page_number);

		if ($page_number > $pages_count) {
			trigger_error(
				"Asked for page number $page_number greater than pages count $pages_count", E_USER_ERROR
			);
		}

		if (count($pages) === 1) {
			return reset($pages);
		}
		if (($pages_count === 1) && isset($pages[Page::UNIQUE])) {
			return $pages[Page::UNIQUE];
		}
		if ((abs($negative) < $page_number) && isset($pages[$negative])) {
			return $pages[$negative];
		}
		if (isset($pages[$page_number])) {
			return $pages[$page_number];
		}
		if (isset($pages[$negative])) {
			return $pages[$negative];
		}
		if (isset($pages[Page::MIDDLE])) {
			return $pages[Page::MIDDLE];
		}
		if (isset($pages[Page::UNIQUE])) {
			return $pages[Page::UNIQUE];
		}
		return reset($pages);
	}

}
