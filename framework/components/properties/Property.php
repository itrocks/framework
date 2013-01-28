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
	 * @getter getType
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

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * @return Type
	 */
	public function getType()
	{
		return new Type($this->type);
	}

}
