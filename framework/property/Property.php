<?php
namespace SAF\Framework;

use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Type;
use SAF\Framework\Tools\Field;
use SAF\Framework\Tools\Names;

/**
 * A property is a field into a programmed class
 */
class Property implements Field
{

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Reflection_Class
	 */
	public $class;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @var Type
	 */
	private $type;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name  string
	 * @param $type  Type
	 * @param $class Reflection_Class
	 */
	public function __construct($name = null, $type = null, $class = null)
	{
		if ($name != null) {
			$this->name = $name;
		}
		if ($type != null) {
			$this->type = $type;
		}
		if ($class != null) {
			$this->class = $class;
		}
	}

	//--------------------------------------------------------------------------------------- display
	/**
	 * @return string
	 */
	public function display()
	{
		return Names::propertyToDisplay($this->name);
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return Type
	 */
	public function getType()
	{
		return $this->type;
	}

}
