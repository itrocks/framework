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

	//---------------------------------------------------------------------------------------- $pages
	/**
	 * @var Page[]
	 */
	public $pages;

}
