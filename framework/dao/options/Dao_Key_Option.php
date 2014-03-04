<?php
namespace SAF\Framework;

/**
 * Dao key option : what class property will be used for key for objects array
 */
class Dao_Key_Option implements Dao_Option
{

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * @var string
	 */
	public $property_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property_name string
	 */
	public function __construct($property_name)
	{
		$this->property_name = $property_name;
	}

}
