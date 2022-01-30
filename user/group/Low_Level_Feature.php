<?php
namespace ITRocks\Framework\User\Group;

/**
 * A detail for a low-level feature
 */
class Low_Level_Feature
{

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * The low-level feature path is like an URI : eg 'ITRocks/Framework/User/output'.
	 * It contains the class path and the name of the feature.
	 *
	 * @var string
	 */
	public $feature;

	//-------------------------------------------------------------------------------------- $options
	/**
	 * @var array
	 */
	public $options = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Low-level feature constructor
	 *
	 * @param $feature string
	 * @param $options array
	 */
	public function __construct($feature = null, array $options = null)
	{
		if (isset($feature)) {
			$this->feature = $feature;
		}
		if (isset($options)) {
			$this->options = $options;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return strval($this->feature);
	}

}
