<?php
namespace SAF\Framework\Dao\Option;

use SAF\Framework\Dao\Option;

/**
 * A Dao group by option
 */
class Group_By implements Option
{

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
	}

}
