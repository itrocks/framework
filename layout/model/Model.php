<?php
namespace ITRocks\Framework\Layout;

use ITRocks\Framework\Layout\Model\Page;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Traits\Has_Name;
use ReflectionException;

/**
 * A print model gives the way to print an object of a given class
 *
 * @business
 * @display layer model
 * @representative class_name, name
 * @store_name layout_models
 */
class Model
{
	use Has_Name;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @mandatory
	 * @user readonly
	 * @var string
	 */
	public $class_name;

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
		return trim($this->class_name . SP . $this->name);
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * @return Reflection_Class
	 * @throws ReflectionException
	 */
	public function getClass()
	{
		return new Reflection_Class($this->class_name);
	}

}
