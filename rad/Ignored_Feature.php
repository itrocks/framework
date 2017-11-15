<?php
namespace ITRocks\Framework\RAD;

/**
 * RAD Ignored feature class
 *
 * @store_name rad_ignored_features
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
