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
	 * @var string
	 */
	private $type;

	//--------------------------------------------------------------------------------------- getName
	public function getName()
	{
		return $this->name;
	}

	//--------------------------------------------------------------------------------------- getType
	public function getType()
	{
		return $this->type;
	}

}
