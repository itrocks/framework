<?php
namespace SAF\Framework\User\Group;

/**
 * A detail for a low-level feature
 */
class Low_Level_Feature
{

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * @var string
	 */
	public $feature;

	//-------------------------------------------------------------------------------------- $options
	/**
	 * @var array
	 */
	public $options;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Low-level feature constructor
	 *
	 * @param $feature string
	 * @param $options array
	 */
	public function __construct($feature = null, $options = [])
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
	public function __toString()
	{
		return strval($this->feature);
	}

}
