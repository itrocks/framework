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
	public string $feature;

	//-------------------------------------------------------------------------------------- $options
	/**
	 * @var array
	 */
	public array $options = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Low-level feature constructor
	 *
	 * @param $feature string|null
	 * @param $options array|null
	 */
	public function __construct(string $feature = null, array $options = null)
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
		return $this->feature;
	}

}
