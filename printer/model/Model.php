<?php
namespace ITRocks\Framework\Printer;

use ITRocks\Framework\Printer\Model\Page;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A print model gives the way to print an object of a given class
 *
 * @business
 * @representative class_name, name
 * @set Printer_Models
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
