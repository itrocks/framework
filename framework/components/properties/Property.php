<?php
namespace SAF\Framework;

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
	public function getName()
	{
		return $this->name;
	}

	//--------------------------------------------------------------------------------------- getName
	public function getType()
	{
		return $this->type;
	}

}
