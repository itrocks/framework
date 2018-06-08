<?php
namespace ITRocks\Framework\Layout;

use ITRocks\Framework\Layout\Model\Page;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A print model gives the way to print an object of a given class
 *
 * @business
 * @display layer model
 * @representative class, name
 * @store_name layout_models
 */
class Model
{
	use Has_Name;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @mandatory
	 * @store string
	 * @var Reflection_Class
	 */
	public $class;

	//---------------------------------------------------------------------------------------- $pages
	/**
	 * @link Collection
	 * @user hide_edit, hide_output
	 * @var Page[]
	 */
	public $pages;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->class ? $this->class->name : '';
	}

}
