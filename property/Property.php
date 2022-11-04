<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Field;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A property is a field into a programmed class
 */
class Property implements Field
{
	use Has_Name;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Reflection_Class|string
	 */
	public Reflection_Class|string $class;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @var Type
	 */
	private Type $type;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name  string|null
	 * @param $type  Type|null
	 * @param $class Reflection_Class|null
	 */
	public function __construct(
		string $name = null, Type $type = null, Reflection_Class $class = null
	) {
		if (isset($class)) $this->class = $class;
		if (isset($name))  $this->name  = $name;
		if (isset($type))  $this->type  = $type;
	}

	//--------------------------------------------------------------------------------------- display
	/**
	 * @return string
	 */
	public function display() : string
	{
		return Names::propertyToDisplay($this->name);
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName() : string
	{
		return $this->name;
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * @return Type
	 */
	public function getType() : Type
	{
		return $this->type;
	}

}
