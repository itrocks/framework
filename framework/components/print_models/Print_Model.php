<?php
namespace SAF\Framework;

/**
 * A print model gives the way to print an object of a given class
 */
class Print_Model
{

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var string
	 */
	public $class;

	//---------------------------------------------------------------------------------------- $pages
	/**
	 * @link Collection
	 * @var Print_Model_Page[]
	 */
	public $pages;

}
