<?php
namespace ITRocks\Framework\Import\Settings;

use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Traits\Has_Name;

/**
 * Import property
 */
class Import_Property
{
	use Has_Name;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var string
	 */
	public $class;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class string
	 * @param $name  string
	 */
	public function __construct($class = null, $name = null)
	{
		if (isset($class)) $this->class = $class;
		if (isset($name))  $this->name  = $name;
	}

	//------------------------------------------------------------------------------------ toProperty
	/**
	 * @return Reflection_Property
	 */
	public function toProperty()
	{
		return new Reflection_Property($this->class, $this->name);
	}

}
