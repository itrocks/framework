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
	public $class_name;

	//---------------------------------------------------------------------------------------- $pages
	/**
	 * @var Page[]
	 */
	public $pages;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 */
	public function __construct($class_name = null)
	{
		if (isset($class_name)) {
			$this->class_name = $class_name;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->dump();
	}

	//------------------------------------------------------------------------------------------ dump
	/**
	 * @return string
	 */
	public function dump()
	{
		$dump = '';
		foreach ($this->pages as $page) {
			$dump .= $page->dump() . LF;
		}
		return $dump;
	}

}
