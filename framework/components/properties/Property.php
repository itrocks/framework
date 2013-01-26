<?php
namespace SAF\Framework;

class Property implements Field
{

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
	 * @param $name string
	 * @param $type Type
	 */
	public function __construct($name = null, $type = null)
	{
		if ($name != null) {
			$this->name = $name;
		}
		if ($type != null) {
			$this->type = $type;
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
