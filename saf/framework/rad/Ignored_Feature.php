<?php
namespace SAF\Framework\RAD;

/**
 * RAD Ignored feature class
 *
 * @set RULE_Ignored_Features
 */
class Ignored_Feature
{

	//----------------------------------------------------------------------------------- $identifier
	/**
	 * @mandatory
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
