<?php
namespace SAF\Framework\RAD;

class Ignored_Feature
{

	//----------------------------------------------------------------------------------- $identifier
	/**
	 * @var string
	 */
	private $identifier;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $identifier string
	 */
	public function __construct($identifier = null)
	{
		if (isset($identifier)) $this->identifier = $identifier;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->identifier;
	}

}
