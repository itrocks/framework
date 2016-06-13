<?php
namespace SAF\Framework\Printer;

use SAF\Framework\Printer\Model\Page;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Traits\Has_Name;

/**
 * A print model gives the way to print an object of a given class
 *
 * @representative class_name, name
 */
class Model
{
	use Has_Name;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @store string
	 * @var Reflection_Class
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
		return $this->class ? $this->class->name : 'Choose a class';
	}

}
