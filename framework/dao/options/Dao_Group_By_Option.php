<?php
namespace SAF\Framework;

/**
 * A Dao group by option
 */
class Dao_Group_By_Option implements Dao_Option
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
			$this->properties = is_array($properties) ? $properties : array($properties);
		}
	}

}
