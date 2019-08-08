<?php
namespace ITRocks\Framework\Dao\Option;

use ITRocks\Framework\Dao\Option;

/**
 * A Dao group by option
 */
class Group_By implements Option
{
	use Has_In;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var string[]
	 */
	public $properties;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $properties string[]|string
	 */
	public function __construct($properties = null)
	{
		if (isset($properties)) {
			$this->properties = is_array($properties) ? $properties : [$properties];
		}
		elseif (!$this->properties) {
			$this->properties = [];
		}
	}

}
