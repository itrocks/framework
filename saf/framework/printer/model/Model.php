<?php
namespace SAF\Framework\Printer;

use SAF\Framework\Printer\Model\Page;

/**
 * A print model gives the way to print an object of a given class
 *
 * @representative class
 */
class Model
{

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var string
	 */
	public $class;

	//---------------------------------------------------------------------------------------- $pages
	/**
	 * @link Collection
	 * @var Page[]
	 */
	public $pages;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->class);
	}

}
